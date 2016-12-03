<?php

use \Swoole\Cache\Redis;
use \Swoole\Client\Client;
use \Swoole\Packet\Format;

class IndexController extends BaseController {

    /**
     * 列表页
     */
    public function indexAction()
    {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $size = isset($_GET['size']) ? $_GET['size'] : 10;
        $plan = isset($_GET['plan']) ? (int)$_GET['plan'] : 0;

        /**
         * @var $redis \Redis
         */
        $redis = Redis::getInstance(C('redis'));

        switch ($plan) {
            case 1:
                $data = $redis->sMembers('tasklist_loop');
                break;
            case 2:
                $data = $redis->sMembers('tasklist_once');
                break;
            case 0:
            default:
                $redis->sUnionStore('tasklist_all', 'tasklist_loop', 'tasklist_once');
                $data = $redis->sMembers('tasklist_all');
                $plan = 0;
                break;
        }

        $out = [];

        $client = new Client();
        $client->connect(C('server')['host'], C('server')['port']);

        if ($data) {
            $Pagination = new ArrayPagination($page, $size);
            $data = $Pagination->open($data);

            foreach ($data['data'] as $value) {
                $info = json_decode($redis->get('task:' . $value), true);
                $out['data'][] = array_merge(['unid' => $value], $info);
            }

            $out['pager'] = $data['pager'];
        }

        $this->assign('list', $out);
        $this->assign('plan', $plan);
        $this->assign('server_status', $client->isConnected());
        $this->display();
    }

    /**
     * 添加
     */
    public function addAction()
    {
        $this->display();
    }

    /**
     * 编辑
     */
    public function editAction()
    {
        $unid = isset($_GET['unid']) ? $_GET['unid'] : '';

        if (empty($unid)) {
            halt('empty unid');
        }

        /**
         * @var $redis \Redis
         */
        $redis = Redis::getInstance(C('redis'));
        $data = $redis->get('task:' . $unid);

        if (empty($data)) {
            halt('任务不存在');
        }

        $this->assign('data', json_decode($data, true));
        $this->assign('unid', $unid);
        $this->display();
    }

    /**
     * 添加/编辑 post接口
     */
    public function taskAction()
    {
        $do = isset($_GET['do']) ? $_GET['do'] : '';
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $plan = isset($_POST['plan']) ? (int)$_POST['plan'] : 0;

        //循环任务
        $start_time_loop = isset($_POST['start_time_loop']) ? $_POST['start_time_loop'] : '';
        $start_time_loop_s = isset($_POST['start_time_loop_s']) ? $_POST['start_time_loop_s'] : '';
        $end_time_loop = isset($_POST['end_time_loop']) ? $_POST['end_time_loop'] : '';
        $end_time_loop_s = isset($_POST['end_time_loop_s']) ? $_POST['end_time_loop_s'] : '';

        //单次任务
        $start_time_once = isset($_POST['start_time_once']) ? $_POST['start_time_once'] : '';
        $start_time_once_s = isset($_POST['start_time_once_s']) ? $_POST['start_time_once_s'] : '';

        $interval = isset($_POST['interval']) ? (int)$_POST['interval'] : 0;
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $task = isset($_POST['task']) ? $_POST['task'] : '';
        $unid = isset($_POST['unid']) ? $_POST['unid'] : '';

        if (empty($name)) {
            $this->ajaxReturn(
                Format::packFormat('', '请填写任务名称', -1)
            );
        }

        $e_date = 0;

        //循环执行
        if ($plan === 1) {
            $s_date = $start_time_loop . ' ' . $start_time_loop_s;
            //检查开始时间格式
            if (empty($s_date) || strcasecmp($s_date, date('Y-m-d H:i:s', strtotime($s_date))) !== 0) {
                $this->ajaxReturn(
                    Format::packFormat('', '无效开始时间', -3)
                );
            }

            if ($end_time_loop) {
                $e_date = $end_time_loop . ' ' . $end_time_loop_s;
                //检查结束时间格式
                if (strcasecmp($e_date, date('Y-m-d H:i:s', strtotime($e_date))) !== 0) {
                    $this->ajaxReturn(
                        Format::packFormat('', '无效结束时间', -4)
                    );
                }

                if (strtotime($s_date) > strtotime($e_date)) {
                    $this->ajaxReturn(
                        Format::packFormat('', '开始时间不能少于结束时间', -5)
                    );
                }
            }

            if (empty($interval)) {
                $this->ajaxReturn(
                    Format::packFormat('', '间隔时间错误', -6)
                );
            }
        } else if ($plan === 2) {
            $s_date = $start_time_once . ' ' . $start_time_once_s;
            //检查时间格式
            if (empty($s_date) || strcasecmp($s_date, date('Y-m-d H:i:s', strtotime($s_date))) !== 0) {
                $this->ajaxReturn(
                    Format::packFormat('', '无效开始时间', -3)
                );
            }
        } else {
            $this->ajaxReturn(
                Format::packFormat('', '无效计划任务', -2)
            );
        }

        if (!filter_var($task, FILTER_VALIDATE_URL) && !is_file($task)) {
            $this->ajaxReturn(
                Format::packFormat('', '无效任务', -7)
            );
        }

        $client = new Client();
        $client->connect(C('server')['host'], C('server')['port']);

        if (!$client->isConnected()) {
            $this->ajaxReturn(
                Format::packFormat('', '无法连接到swoole服务，添加任务失败', -8)
            );
        }

        $data = [
            'do'            => $do,
            'unid'          => $unid,
            'name'          => $name,
            'start_time'    => $s_date,
            'end_time'      => $e_date,
            'plan'          => $plan,
            //second
            'interval'      => $interval,
            'task'          => $task,
            'description'   => $description,
            'status'        => 1,
            'addtime'       => date('Y-m-d H:i:s')
        ];

        $result = $client->send($data);

        $this->ajaxReturn($result);
    }

    /**
     * 修改运行状态
     */
    public function statusAction()
    {
        $unid = isset($_POST['unid']) ? $_POST['unid'] : '';
        $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

        if (empty($unid)) {
            $this->ajaxReturn(
                Format::packFormat('', '缺少id', -1)
            );
        }

        /**
         * @var $redis \Redis
         */
        $redis = Redis::getInstance(C('redis'));
        $data = $redis->get('task:' . $unid);

        if (empty($data)) {
            $this->ajaxReturn(
                Format::packFormat('', '无效任务', -1)
            );
        }

        $data = json_decode($data, true);

        $send_data = [
            'start_time'    => $data['start_time'],
            'plan'          => $data['plan'],
            'end_time'      => $data['end_time'],
            'do'            => 'status',
            'status'        => $status,
            'unid'          => $unid
        ];

        $client = new Client();
        $client->connect(C('server')['host'], C('server')['port']);

        if (!$client->isConnected()) {
            $this->ajaxReturn(
                Format::packFormat('', '无法连接到swoole服务，添加任务失败', -8)
            );
        }

        $result = $client->send($send_data);

        $this->ajaxReturn($result);
    }

}
