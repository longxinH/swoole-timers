<?php

use \Swoole\Server\Server;
use \Swoole\Packet\Format;
use \Swoole\Client\Client;
use \Swoole\Cache\Redis;

include '../../vendor/autoload.php';

class TimersServer extends Server {

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * 注册 \swoole_process
     */
    public function afterStart()
    {
        $process = new \swoole_process(function (\swoole_process $process) {
            /**
             * @var $redis \Redis
             */
            $redis = Redis::getInstance($this->config['redis']);
            while (true) {
                $client = new Client();
                $client->connect($this->host, $this->port);

                if (intval($redis->get('task_num')) < $this->config['swoole']['task_worker_num']) {
                    //todo 阻塞获取队列
                    $mq = $redis->brPop('timerslist', 0);

                    if ($mq && isset($mq[1])) {
                        //todo  send client task
                        $client->send([
                            'params'    => $mq[1],
                            'cmd'       => 'task'
                        ]);
                    }
                }
            }
        });

        $this->resetTask();

        $this->server->addProcess($process);
    }

    /**
     * @param swoole_server $server
     * @param int $fd
     * @param int $from_id
     * @param array $data
     * @param array $header
     * @return array
     */
    public function doWork(\swoole_server $server, $fd, $from_id, $data, $header)
    {
        $redis = Redis::getInstance($this->config['redis']);

        //校验开始时间
        if (strcasecmp($data['start_time'], date('Y-m-d H:i:s', strtotime($data['start_time']))) !== 0) {
            return Format::packFormat('', 'start time error', -1);
        }

        if (!in_array($data['plan'], [1, 2])) {
            return Format::packFormat('', 'error plan type', -2);
        }

        if ($data['plan'] == 1) {
            if ($data['end_time'] != 0
                && strtotime($data['end_time']) - time() <= 0
                && strcasecmp($data['end_time'], date('Y-m-d H:i:s', strtotime($data['end_time']))) !== 0) {
                return Format::packFormat('', 'end time error', -3);
            }
        }

        if ($data['do'] == 'add') {
            $unid = uniqid() . rand(10000, 99999);

            $redis_data = [
                'name'              => $data['name'],
                'start_time'        => $data['start_time'],
                'end_time'          => $data['end_time'],
                'plan'              => $data['plan'],
                'interval'          => $data['interval'],
                'task'              => $data['task'],
                'description'       => $data['description'],
                'status'            => 1,
                'addtime'           => $data['addtime'],
                'last_run_start'    => '',
                'last_run_end'      => '',
                'run_number'        => 0
            ];

            /**
             * 后台使用redis列表
             * tasklist_loop  循环执行管理列表
             * tasklist_once  单次执行管理列表
             */
            $task_type = $data['plan'] === 1 ? 'tasklist_loop' : 'tasklist_once';

            //todo 写入循环执行管理列表
            $redis->sAdd($task_type, $unid);
            //todo 记录详细信息
            $redis->set('task:' . $unid, json_encode($redis_data));
            //todo 唯一id写入队列
            $redis->lPush('timerslist', $unid);

            return Format::packFormat($unid, 'add mq success');

        } else if ($data['do'] == 'edit') {
            $unid = $data['unid'];
            if (empty($unid)) {
                return Format::packFormat('', 'empty unid', -4);
            }

            $old_data = $redis->get('task:' . $unid);

            if (empty($old_data)) {
                return Format::packFormat('', 'task not exist', -5);
            }

            $tmp_data = [
                'name'              => $data['name'],
                'start_time'        => $data['start_time'],
                'end_time'          => $data['end_time'],
                'plan'              => $data['plan'],
                'interval'          => $data['interval'],
                'task'              => $data['task'],
                'description'       => $data['description']
            ];

            //todo 标记任务被编辑过
            $redis->set('task:' . $unid . '_up', json_encode($tmp_data));

            return Format::packFormat($unid, 'edit mq success');

        } else if ($data['do'] == 'status') {
            $unid = $data['unid'];
            if (empty($unid)) {
                return Format::packFormat('', 'empty unid', -4);
            }

            $old_data = $redis->get('task:' . $unid);

            if (empty($old_data)) {
                return Format::packFormat('', 'task not exist', -5);
            }

            $tmp_data = [
                'status'    => intval($data['status'])
            ];

            //todo 标记更新状态
            $redis->set('task:' . $unid . '_up_status', json_encode($tmp_data));

            return Format::packFormat($unid, 'update status success');

        } else {
            return Format::packFormat('', 'error do', -99);
        }

    }

    /**
     * @param swoole_server $server
     * @param $task_id
     * @param $from_id
     * @param $data
     * @return string
     */
    public function doTask(\swoole_server $server, $task_id, $from_id, $data)
    {
        $redis = Redis::getInstance($this->config['redis']);

        $redis->incr('task_num');

        if (empty($data['params'])) {
            $redis->decr('task_num');
            return 'error';
        }

        $unid = $data['params'];
        $data = $redis->get('task:' . $unid);

        if (empty($data)) {
            $redis->decr('task_num');
            return null;
        }

        $data = json_decode($data, true);
        $queue = true;

        //todo 判断是否存在标记被编辑过
        if ($tmp_data = $redis->get('task:' . $unid . '_up')) {
            $data = array_merge($data, json_decode($tmp_data, true));
            $redis->del('task:' . $unid . '_up');
        }

        //todo 判断是否存在标记修改状态
        if ($status_data = $redis->get('task:' . $unid . '_up_status')) {
            $data = array_merge($data, json_decode($status_data, true));
            $redis->del('task:' . $unid . '_up_status');
        }

        if (intval($data['status']) === 1) {
            //首次运行
            if ($data['run_number'] === 0) {
                if (strtotime($data['start_time']) - time() <= 0) {
                    //todo 记录最后运行时间起始
                    $data['last_run_start'] = date('Y-m-d H:i:s');

                    if ($this->runTimingTask($unid, $data['plan'], $data['task']) === false) {
                        //todo log
                    }

                    //一次执行 不写入队列
                    if ($data['plan'] == 2) {
                        $queue = false;
                    }

                    $data['last_run_end'] = date('Y-m-d H:i:s');
                    $data['run_number']++;
                }
            } else {
                //todo 没有结束时间 && 没有到结束时间
                if ($data['end_time'] == 0 || strtotime($data['end_time']) - time() >= 0) {
                    //todo 超过开始时间 && 超过定时
                    if (time() - strtotime($data['start_time']) >= 0 && strtotime($data['last_run_end']) + $data['interval'] <= time()) {
                        //todo 记录最后运行时间起始
                        $data['last_run_start'] = date('Y-m-d H:i:s');

                        if ($this->runTimingTask($unid, $data['plan'], $data['task']) === false) {
                            //todo log
                        }

                        //一次执行 不写入队列
                        if ($data['plan'] == 2) {
                            $queue = false;
                        }

                        $data['last_run_end'] = date('Y-m-d H:i:s');
                        $data['run_number']++;
                    }
                } else if ($data['end_time'] != 0 && strtotime($data['end_time']) - time() < 0) {
                    //todo 任务已过有效时间
                    $queue = false;
                }
            }
        } else {
            $queue = false;
        }

        $redis->set('task:' . $unid, json_encode($data));

        if ($queue === true) {
            //todo 写入队列
            $redis->lPush('timerslist', $unid);
        }

        $redis->decr('task_num');

        return 'ok';

    }

    /**
     * 执行定时任务
     * @param $unid
     * @param $plan
     * @param $task
     * @return bool
     */
    private function runTimingTask($unid, $plan, $task)
    {
        try {
            if (filter_var($task, FILTER_VALIDATE_URL)) {
                file_get_contents($task);
            } else if (is_file($task)) {
                $task = file_get_contents($task);
                $task = ltrim($task, '<?php');
                $task = ltrim($task, '<?');
                $task = rtrim($task, '?>');
                @eval($task);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 重置任务  用于防止服务异常中断 导致队列掉失
     */
    private function resetTask()
    {
        $redis = new \Redis();
        $redis->connect($this->config['redis']['host'], $this->config['redis']['port']);

        $mq = $redis->lRange('timerslist', 0, -1);
        $loop = $redis->sMembers('tasklist_loop');
        $once = $redis->sMembers('tasklist_once');

        if ($loop) {
            foreach ($loop as $value) {
                if (!in_array($value, $mq)) {
                    $loop_mq = json_decode($redis->get('task:' . $value), true);

                    if ($loop_mq['end_time'] == 0 || strtotime($loop_mq['end_time']) - time() >= 0) {
                        $redis->lPush('timerslist', $value);
                    }
                }
            }
        }

        if ($once) {
            foreach ($once as $value) {
                if (!in_array($value, $mq)) {
                    $once_mq = json_decode($redis->get('task:' . $value), true);

                    if ($once_mq['end_time'] == 0 || strtotime($once_mq['end_time']) - time() >= 0) {
                        $redis->lPush('timerslist', $value);
                    }
                }
            }
        }
    }

}

/*
 * 项目所在目录
 */
define('PROJECT_ROOT', dirname(__DIR__));

$server = new TimersServer('../config/swoole.ini', 'timers');
$server->run();

