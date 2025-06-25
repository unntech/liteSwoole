<?php

namespace App\framework;

use App\framework\extend\Redis;
use LiPhp\LiComm;
use App\structure\TaskData;

class WebSocket extends AppBase
{
    protected \Swoole\WebSocket\Server | \Swoole\Server | null $server;
    protected int $fd;
    protected \Swoole\WebSocket\Frame $frame;
    protected array $data;
    protected string $uri;
    public bool $exitFlag = false;
    protected Response $response_handle;

    use \App\traits\crypt;

    public function __construct(\Swoole\WebSocket\Server | \Swoole\Server | null $server = null)
    {
        parent::__construct();
        if(!is_null($server)){
            $this->server = $server;
        }
        $this->response_handle = new Response(['return_data'=>true]);
    }

    /**
     * Api请求日志记录表，生产环境建议单独记录（如mongodb库）以减少主库压力
     * @return bool|int
     */
    protected function msgLog()
    {

    }

    /**
     * 解密验签数据
     */
    protected function initialize()
    {
        if($this->data){
            $check = $this->verifySign($this->data, false);  //如果需要安全验证，要求必须有签名才可以请求，把perforce参数改为true
            if($check === false){
                $this->error(405, '数据验签失败！', ['request'=>$this->data]);
                return;
            }
        }

        /* ---------------
        其它鉴权等
        //--------------*/
    }

    protected function init_frame_data($frame): void
    {
        $this->frame = $frame;
        $this->fd = $frame->fd;
        $this->data = json_decode($frame->data, true) ?? [];
        $uri = $this->data['head']['uri'] ?? '/';
        $this->uri = (str_starts_with($uri, '/') ? $uri : '/' . $uri);
        if(isset($this->data['signType'])){
            $this->response_options(['signType'=>$this->data['signType']]);
        }
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

    public function success(array $data = [], int $errcode = 0, string $msg = 'success')
    {
        $response_data = $this->response_handle->success($data, $errcode, $msg);
        $this->server->push($this->fd, json_encode($response_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function error(int $errcode = 0, string $msg = 'fail', array $data = ['void'=>null])
    {
        $this->exitFlag = true;
        $response_data = $this->response_handle->error($errcode, $msg, $data);
        $this->server->push($this->fd, json_encode($response_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function exitFlag(bool $flag = true): void
    {
        $this->exitFlag = $flag;
    }

    public function set_fd(int $fd): void
    {
        $this->fd = $fd;
    }

    /**
     * onMessage方法运行入口
     * @return mixed|null
     */
    final public function run($frame)
    {
        $this->msgLog();
        if($frame->opcode != WEBSOCKET_OPCODE_TEXT){
            //本例子不处理非文本数据请求
            return ;
        }
        $this->init_frame_data($frame);
        $requestPath = explode('/', $this->uri);
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
        $newClass = "App\\controller\\WebSocket\\" . $action;
        try{
            $filename = DT_ROOT. '/app/controller/WebSocket/';
            $filename .= str_replace("\\", '/', $action) . '.php';
            if(file_exists($filename)){

                $api = new $newClass();
                $api->server = $this->server;
                $api->init_frame_data($frame);
                if($api->exitFlag) return $api;
                if($action != 'index' && $func != 'authorize'){
                    $api->initialize();
                    if($api->exitFlag) return $api;
                }
                $api->$func();

                return $api;

            }else{
                swoole_error_log(5, "WebSocket Controller not found! {$newClass} {$func}");
                $this->error(404,'接口不存在！');
            }

        }catch(\Throwable $e){
            $emsg = $e->getMessage();
            if(DT_DEBUG){
                $data = ['request'=>$this->data, 'exception'=>$e, 'code'=>$e->getCode(),'message'=>$e->getMessage(), 'trace'=>$e->getTrace()];
            }else{
                $data = ['code'=>$e->getCode(),'message'=>$e->getMessage()];
            }
            swoole_error_log(5, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->error(417, $emsg, $data);
        }
        return $this;
    }

    public function task(string $uri, mixed $data, int $fd = 0)
    {
        $data = new TaskData($uri, $data, $fd);
        return $this->server->task($data);
    }

    public function __call($name, $arguments) {
        //方法名$name区分大小写

        return $this->error(400, "调用方法：{$name} 不存在");
    }

    /**
     * 验签
     * @param array $data
     * @param bool $perforce
     * @return bool
     */
    public function verifySign(array &$data, bool $perforce = false) : bool
    {

        return $this->response_handle->verifySign($data, $perforce);

    }

    public function authorize_access_token(?string $appid, ?string $secret): array
    {
        if(empty($appid)){
            return ['suc'=>false, 'msg'=>'Appid 不能为空'];
        }
        $r = $this->db->table('app_secret')->where(['appid'=>$appid])->selectOne();
        if($r){
            if($r['status'] < 1 || ($r['status'] == 2 && $r['expires'] < $this->DT_TIME) || $r['appsecret'] != $secret){
                return ['suc'=>false, 'msg'=>'Appid 状态不正常或secret不正确'];
            }else{
                $exp = $this->DT_TIME + 7200;
                $jwt = ['sub'=>$appid, 'exp'=>$exp];
                $token = $this->getToken($jwt);
                $_str = serialize(['token'=>$token, 'secret'=>$secret, 'fd'=>$this->fd]);
                Redis::set("ACCESS_".$appid, $_str, 7200);
                return [
                    'suc'          => true,
                    'access_token' => $token,
                    'expires_in'   => $exp,
                ];
            }
        }else{
            return ['suc'=>false, 'msg'=>'Appid 不存在'];
        }
    }

    public function access_token_generate(?string $appid): array
    {
        if(empty($appid)){
            return ['suc'=>false, 'msg'=>'Appid 不能为空'];
        }
        if(strlen($appid) < 32){
            return ['suc'=>false, 'msg'=>'请求设备UUID长度不能小于32位'];
        }
        $exp = $this->DT_TIME + 7200;
        $jwt = ['sub'=>$appid, 'exp'=>$exp];
        $token = $this->getToken($jwt);
        $secret = LiComm::createNonceStr(32);
        $_str = serialize(['token'=>$token, 'secret'=>$secret, 'fd'=>$this->fd]);
        Redis::set("ACCESS_".$appid, $_str, 7200);
        return [
            'suc'          => true,
            'access_token' => $token,
            'expires_in'   => $exp,
            'secret'       => $secret,
        ];
    }

}