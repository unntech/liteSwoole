<?php

namespace App\controller;

use App\framework\AppBase;

class BootStrap extends AppBase
{
    public function __construct()
    {
        parent::__construct();
        $this->DT_TIME = time();

    }

    /**
     * 进程启动
     * @param \Swoole\Server $server
     * @param int $workerId
     * @return void
     */
    public function onWorkerStart(\Swoole\Server $server, int $workerId)
    {
        //echo 'Server is Reload...';

    }

    /**
     * 程序启动的第一进程
     * 一般用于加载
     * @param \Swoole\Server $server
     * @param int $workerId
     * @return void
     */
    public function onWorkerStartOnce(\Swoole\Server $server, int $workerId)
    {
        //echo 'Server is StartOnce...';

        /* ----  定时任务示例
        $timerId = \Swoole\Timer::tick(5000, function () use ($server){
            $quoteTaskNum = LiApp::$commTable->incr('quoteTaskNum', 'num');
            $task_id = $server->task( ['action'=>'Job10Sec'] );
        });
        // ------- */
    }


    /**
     * 停止服务时
     * @param \Swoole\Server $server
     * @return void
     */
    public function onShutdown(\Swoole\Server $server)
    {
        $this->db->query('TRUNCATE TABLE ws_clients');
    }

    /**
     * 任务调用执行
     * @param $server
     * @param int $task_id
     * @param int $src_worker_id
     * @param mixed $data
     * @return mixed
     */
    public function onTask($server, int $task_id, int $src_worker_id, mixed $data)
    {
        //$quoteTaskNum = LiApp::$commTable->decr('quoteTaskNum', 'num');
        //echo $task_id.': '.json_encode($data).$quoteTaskNum.PHP_EOL;

        $action = $data['action'] ?? 'NONE';
        // 根据不同的指令 处理不同的任务
//        switch ($action){
//
//            default:
//        }

        return [$task_id, $data];  //把数据返回给 TaskFinish 处理
    }

    /**
     * 任务执行完成
     * @param $server
     * @param int $task_id
     * @param mixed $data
     * @return void
     */
    public function onTaskFinish($server, int $task_id, mixed $data)
    {
        //echo 'Finished: '.json_encode($data).PHP_EOL;

    }
}