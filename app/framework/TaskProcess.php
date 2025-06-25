<?php

namespace App\framework;

use App\structure\TaskData;

class TaskProcess extends AppBase
{
    protected $server;
    protected TaskData $taskData;
    protected int $task_id;
    protected int $src_worker_id;
    public bool $exitFlag = false;
    protected Response $response_handle;
    protected array $errors;

    use \App\traits\crypt;

    public function __construct($server = null)
    {
        parent::__construct();
        if(!is_null($server)){
            $this->server = $server;
        }
        $this->response_handle = new Response(['return_data'=>true, 'json_encode_flags'=>JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE]);
    }

    /**
     * Task请求日志记录表，生产环境建议单独记录（如mongodb库）以减少主库压力
     * @return bool|int
     */
    protected function taskLog()
    {

    }

    protected function init_task_data($taskData): void
    {
        $this->taskData = $taskData;

    }

    public function taskData(): TaskData
    {
        return $this->taskData;
    }

    public function response_options(array $options = []): Response
    {
        return $this->response_handle->setOptions($options);
    }

    /**
     * 设置输出公共 header 参数值
     * @param array $headers
     * @return Response
     */
    public function headers(array $headers = []): Response
    {
        return $this->response_handle->headers($headers);
    }

    public function exitFlag(bool $flag = true): void
    {
        $this->exitFlag = $flag;
    }

    public function push(array $data)
    {
        if($this->taskData->fd > 0 && $this->server->exist($this->taskData->fd)){
            return $this->server->push($this->taskData->fd, json_encode($data, JSON_UNESCAPED_SLASHES));
        }
    }

    /**
     * onTask方法运行入口
     * @return mixed|null
     */
    final public function run(TaskData $taskData, int $task_id, int $src_worker_id)
    {
        $this->task_id = $task_id;
        $this->src_worker_id = $src_worker_id;
        return $this->_run($taskData,'Task');

    }

    /**
     * onTaskFinish方法运行入口
     * @return mixed|null
     */
    final public function finish(TaskData $taskData, int $task_id)
    {
        $this->task_id = $task_id;
        return $this->_run($taskData, 'TaskFinish');

    }

    private function _run(TaskData $taskData, string $path = 'Task')
    {
        $this->taskLog();

        $this->init_task_data($taskData);
        $requestPath = explode('/', $this->taskData->uri);
        $pathInfoCount = count($requestPath);
        if($pathInfoCount < 3){
            if(empty($requestPath[1])){
                $requestPath = ['', 'index', 'index'];
            }else{
                $requestPath = ['', 'index', $requestPath[1]];
            }
        }

        $_func = array_pop($requestPath);
        $_i = strpos($_func, '.');
        $func = $_i === false ? $_func : substr($_func, 0, $_i);
        unset($requestPath[0]);
        $action = implode("\\", $requestPath);
        $newClass = "App\\controller\\{$path}\\" . $action;
        try{
            $filename = DT_ROOT. "/app/controller/{$path}/";
            $filename .= str_replace("\\", '/', $action) . '.php';
            if(file_exists($filename)){

                $api = new $newClass();
                $api->server = $this->server;
                $api->task_id = $this->task_id;
                if($path == 'Task'){
                    $api->src_worker_id = $this->src_worker_id;
                }
                $api->init_task_data($taskData);
                if($api->exitFlag) return $api;

                $api->$func();

                return $api;

            }else{
                $this->taskData->status(2);
                $this->errors[] = 'Task controller not found!';
                $this->taskData->record['errors'][] = 'Task controller not found!';
                swoole_error_log(5, "Task controller not found! {$newClass} {$func}");
            }

        }catch(\Throwable $e){
            if(DT_DEBUG){
                $data = ['request'=>$this->taskData, 'exception'=>$e, 'code'=>$e->getCode(),'message'=>$e->getMessage(), 'trace'=>$e->getTrace()];
            }else{
                $data = ['code'=>$e->getCode(),'message'=>$e->getMessage()];
            }
            $this->errors[] = $data;
            swoole_error_log(5, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function __call($name, $arguments) {
        //方法名$name区分大小写
        $this->errors[] = '调用方法：{$name} 不存在';
        swoole_error_log(4, "Task method not found! {$name} ". json_encode($arguments, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return $this;
    }

}