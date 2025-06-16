<?php
require 'autoload.php';

use LiPhp\Config;
use App\framework\LiApp;
use App\framework\HttpRequest;
use App\controller\BootStrap;

Co::set(['hook_flags'=> SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP | SWOOLE_HOOK_CURL]);

$app = new LiteSwoole(Config::get('swoole'));
$app->start();
class LiteSwoole
{
    protected $service;
    //ipv6 的 $host = '::', 协义使用：SWOOLE_SOCK_TCP6，Linux系统下监听IPv6端口后使用IPv4地址也可以进行连接
    protected string $host = '0.0.0.0';
    protected int $port = 9898;
    protected string $taskName = 'LiteSwoole';
    protected array $options = [
        'log_file' => __DIR__.'/log/app.log',
        'pid_file' => __DIR__.'/app.pid',
    ];

    public function __construct($param = [])
    {
        $this->port = $param['port'] ?? 9899;
        $sock_type = SWOOLE_SOCK_TCP;
        if(isset($param['IPV6']) && $param['IPV6']=== true){
            $sock_type = $sock_type | SWOOLE_SOCK_TCP6;
        }
        if(isset($param['SSL']) && $param['SSL']=== true){
            $sock_type = $sock_type | SWOOLE_SSL;
        }
        $run_services = array_map('strtolower', $param['services']);
        if(in_array('websocket', $run_services)){
            $this->service = new Swoole\WebSocket\Server($this->host, $this->port, SWOOLE_PROCESS, $sock_type );
        }else{

            $this->service = new Swoole\Http\Server($this->host, $this->port, SWOOLE_PROCESS, $sock_type );
        }

        if (!empty($param)) {
            $this->taskName = $param['taskName'];
            $options = $param['options'];
            $this->options = array_merge($this->options, $options);
        }
        $this->service -> set($this->options);

        LiApp::$commTable = new Swoole\Table(1024);
        LiApp::$commTable->column('num', Swoole\Table::TYPE_INT);
        LiApp::$commTable->create();

        foreach ($run_services as $s) {
            $s = strtolower($s);
            if($s == 'http'){
                $this->service->on("request",    [$this, 'onRequest']);
            }
            if($s == 'task'){
                $this->service->on("Task",       [$this, 'onTask']);
                $this->service->on("Finish",     [$this, 'onTaskFinish']);
            }
            if($s == 'websocket'){
                $this->service->on("open",       [$this, 'onOpen']);
                $this->service->on("message",    [$this, 'onMessage']);
                $this->service->on("close",      [$this, 'onClose']);
            }
        }

        $this->service->on('WorkerStart', function (Swoole\Server $server, int $workerId){
            LiApp::worker_begin();
            $boot = new BootStrap();
            $boot->onWorkerStart($server, $workerId);

            if (!$server->taskworker && $workerId == 0) {
                $boot->onWorkerStartOnce($server, $workerId);
            }
            LiApp::worker_end();
        });

        $this->service->on('Start', function(Swoole\Server $server){
            swoole_set_process_name($this->taskName);
        });

        $this->service->on('shutdown', function (Swoole\Server $server) {
            LiApp::worker_begin();
            $boot = new BootStrap();
            $boot->onShutdown($server);
            LiApp::worker_end();
        });
    }

    public function onRequest(Swoole\Http\Request $request, Swoole\Http\Response $response): void
    {
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            $response->status(200);
            $response->header('Content-Type', 'image/x-icon');
            $response->sendfile(DT_ROOT.'/favicon.ico');
            return;
        }

        LiApp::worker_begin();
        $http = (new HttpRequest($request))->run();

        LiApp::worker_end();
        $res = $http ? json_encode($http->response_data(), JSON_UNESCAPED_SLASHES) : '{"head":{"errcode":500,"msg":"Request Throwable!","unique_id":"aFA7K2tt4FH7r5sB-xmS3QAAAIg","timestamp":1750088491},"body":{"data":""},"signType":"NONE","encrypted":false,"bodyEncrypted":""}';
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header("Content-Type", "application/json; charset=utf-8");
        $response->end($res);
    }

    public function onTask($server, int $task_id, int $src_worker_id, mixed $data)
    {
        LiApp::worker_begin();
        $boot = new BootStrap();
        $ret = $boot->onTask($server, $task_id, $src_worker_id, $data);
        LiApp::worker_end();
        return $ret;
    }

    public function onTaskFinish($server, int $task_id, mixed $data)
    {
        LiApp::worker_begin();
        $boot = new BootStrap();
        $boot->onTaskFinish($server, $task_id, $data);
        LiApp::worker_end();
    }

    public function onOpen(Swoole\WebSocket\Server $server, Swoole\Http\Request $request)
    {
        LiApp::worker_begin();
        $ws = new \App\controller\WebSocketOpen($server);
        $ws->open($request);
        LiApp::worker_end();
    }

    public function onMessage(Swoole\WebSocket\Server $server, Swoole\WebSocket\Frame $frame)
    {
        // Swoole 自动处理 PING/PONG，无需手动处理
        //if ($frame->opcode === WEBSOCKET_OPCODE_PING) {
        //    // 回复 PONG 帧（opcode 0xA）
        //    $server->push($frame->fd, $frame->data, WEBSOCKET_OPCODE_PONG);
        //    return;
        //}
        if(!$frame->finish){ return; }  //数据包未接收完整，不处理
        if($frame->opcode == WEBSOCKET_OPCODE_TEXT){
            if(in_array($frame->data, [null, '', '{}', '[]'])){
                $server->push($frame->fd, $frame->data);
                return;
            }
            if(strtolower($frame->data) == 'ping'){
                $server->push($frame->fd, 'pong');
                return;
            }
        }
        LiApp::worker_begin();
        $ws = (new \App\framework\WebSocket($server))->run($frame);

        LiApp::worker_end();
    }

    public function onClose(Swoole\Server $server, int $fd, int $reactorId)
    {
        LiApp::worker_begin();
        $ws = new \App\controller\WebSocketClose($server);
        $ws->close($fd, $reactorId);
        LiApp::worker_end();
    }

    public function start(): void
    {
        $this->service->start();
    }
}