<?php

namespace App\controller\TaskFinish;

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

        $this->taskData->status(9);
    }
}