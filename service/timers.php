<?php

use \Swoole\Server\Tcp;
use \Swoole\Packet\Format;

include '../vendor/autoload.php';

class TimersServer extends Tcp {

    /**
     * @var \Redis
     */
    protected $redis;

    public function onWorkerStart(\swoole_server $server, $workerId)
    {
        $this->redis = new \Swoole\Cache\Redis('127.0.0.1', 6379);
    }

    /**
     * @param swoole_server $server
     * @param $fd
     * @param $from_id
     * @param $data
     * @return mixed
     */
    public function doWork(\swoole_server $server, $fd, $from_id, $data)
    {
        $data = json_decode($data, true);

        //todo 执行队列异步任务
        if (isset($data['cmd']) && $data['cmd'] == 'task') {
            $task_data = array_merge($data, ['fd' => $fd]);

            $server->task($task_data);
            return 'task success';

        //添加任务
        } else {
            //校验开始时间
            if (strcasecmp($data['start_time'], date('Y-m-d H:i:s', strtotime($data['start_time']))) !== 0) {
                return json_encode(Format::packFormat('', 'start time error', -1));
            }

            if (!in_array($data['plan'], [1, 2])) {
                return json_encode(Format::packFormat('', 'error plan type', -2));
            }

            if ($data['plan'] == 1) {
                if ($data['end_time'] != 0
                    && strtotime($data['end_time']) - time() <= 0
                    && strcasecmp($data['end_time'], date('Y-m-d H:i:s', strtotime($data['end_time']))) !== 0) {
                    return json_encode(Format::packFormat('', 'end time error', -3));
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
                $this->redis->sAdd($task_type, $unid);
                //todo 记录详细信息
                $this->redis->set('task:' . $unid, json_encode($redis_data));
                //todo 唯一id写入队列
                $this->redis->lPush('timerslist', $unid);

                return json_encode(Format::packFormat($unid, 'add mq success'));

            } else if ($data['do'] == 'edit') {
                $unid = $data['unid'];
                if (empty($unid)) {
                    return json_encode(Format::packFormat('', 'empty unid', -4));
                }

                $old_data = $this->redis->get('task:' . $unid);

                if (empty($old_data)) {
                    return json_encode(Format::packFormat('', 'task not exist', -5));
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
                $this->redis->set('task:' . $unid . '_up', json_encode($tmp_data));

                return json_encode(Format::packFormat($unid, 'edit mq success'));

            } else if ($data['do'] == 'status') {
                $unid = $data['unid'];
                if (empty($unid)) {
                    return json_encode(Format::packFormat('', 'empty unid', -4));
                }

                $old_data = $this->redis->get('task:' . $unid);

                if (empty($old_data)) {
                    return json_encode(Format::packFormat('', 'task not exist', -5));
                }

                $tmp_data = [
                    'status'    => intval($data['status'])
                ];

                //todo 标记更新状态
                $this->redis->set('task:' . $unid . '_up_status', json_encode($tmp_data));

                return json_encode(Format::packFormat($unid, 'update status success'));

            } else {
                return json_encode(Format::packFormat('', 'error do', -99));
            }
        }


    }

    /**
     * @param swoole_server $server
     * @param $task_id
     * @param $from_id
     * @param $task_data
     * @return string
     */
    public function onTask(\swoole_server $server, $task_id, $from_id, $task_data)
    {
        $this->redis->incr('task_num');

        if (empty($task_data['unid'])) {
            $this->redis->decr('task_num');
            return [
                'fd'        => $task_data['fd'],
                'message'   => 'error'
            ];
        }

        $unid = $task_data['unid'];
        $data = $this->redis->get('task:' . $unid);

        if (empty($data)) {
            $this->redis->decr('task_num');
            return [
                'fd'        => $task_data['fd'],
                'message'   => 'error'
            ];
        }

        $data = json_decode($data, true);
        $queue = true;

        //todo 判断是否存在标记被编辑过
        if ($tmp_data = $this->redis->get('task:' . $unid . '_up')) {
            $data = array_merge($data, json_decode($tmp_data, true));
            $this->redis->del('task:' . $unid . '_up');
        }

        //todo 判断是否存在标记修改状态
        if ($status_data = $this->redis->get('task:' . $unid . '_up_status')) {
            $data = array_merge($data, json_decode($status_data, true));
            $this->redis->del('task:' . $unid . '_up_status');
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
            //$queue = false;
        }

        $this->redis->set('task:' . $unid, json_encode($data));

        if ($queue === true) {
            //todo 写入队列
            $this->redis->lPush('timerslist', $unid);
        }

        $this->redis->decr('task_num');

        return [
            'fd'        => $task_data['fd'],
            'message'   => 'success'
        ];

    }

    /**
     * task_worker完成
     * @param \swoole_server $server
     * @param $task_id
     * @param $data
     */
    public function onFinish(\swoole_server $server, $task_id, $data)
    {
        $fd = $data['fd'];
        $message = $data['message'];
        $server->send($fd, 'Message : ' . $message);
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
    public function resetTask()
    {
        $redis = new \Swoole\Cache\Redis('127.0.0.1', 6379);

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

$server = new TimersServer('0.0.0.0:9501', 'timers');

/*
 * 设置Pid存放路径
 */
$server->setPidPath(__DIR__ . '/run');

/*
 * 注册队列
 */
$server->addProcess(
    \Swoole\Console\Process::createProcess(
        function (\swoole_process $process) use ($server) {
            /**
             * @var $redis \Redis
             */
            $redis = new \Swoole\Cache\Redis('127.0.0.1', 6379);
            while (true) {
                $client = new \Swoole\Client\Sync\Tcp('0.0.0.0:9501');

                if (intval($redis->get('task_num')) < 20) {
                    //todo 阻塞获取队列
                    $mq = $redis->brPop('timerslist', 0);

                    if ($mq && isset($mq[1])) {
                        //todo send client task
                        $client->recv(function (\swoole_client $client, $data) {

                        })->send(
                            json_encode([
                                'unid'    => $mq[1],
                                'cmd'       => 'task'
                            ])
                        );
                    }
                }
            }
        }
    )
);

$server->resetTask();

$server->run([
    'worker_num'            => 4,
    'task_worker_num'       => 20,
    'max_request'           => 5000,
    'dispatch_mode'         => 3,
    'log_file'              => "/tmp/swoole-timers-server-0.0.0.0_9501.log",
    //todo 守护进程改成1
    'daemonize'             => 0
]);

