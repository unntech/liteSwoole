<?php

namespace App\controller\Task;

use App\framework\TaskProcess;

class index extends TaskProcess
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        echo json_encode($this->taskData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE). PHP_EOL;

        // 状态设为：9 代表完结，不再调 onTaskFinish
        $this->taskData->status(3);
        $this->push($this->taskData->data);
    }

    public function test(): void
    {
        echo json_encode($this->taskData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE). PHP_EOL;

        $this->taskData->status(9);
    }
}