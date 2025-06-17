<?php

namespace App\controller;

use App\framework\AppBase;
use App\framework\TaskProcess;
use App\structure\TaskData;
use App\framework\LiApp;

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
            $task_id = $server->task( new TaskData('/index/test', ['text'=>'StartOnce']) );
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
     * @return mixed|void
     */
    public function onTask($server, int $task_id, int $src_worker_id, mixed $data)
    {
        //$quoteTaskNum = LiApp::$commTable->decr('quoteTaskNum', 'num');
        //echo $task_id.': '.json_encode($data).PHP_EOL;

        $task = (new TaskProcess($server))->run($data, $task_id, $src_worker_id);
        //echo json_encode($task->errors());
        $taskData = $task->taskData();
        if($taskData->status == 9){
            return ;
        }else{
            return $taskData;
        }
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

        $task = (new TaskProcess($server))->finish($data, $task_id);

    }
}