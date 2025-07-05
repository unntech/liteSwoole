<?php

namespace App\framework;

use App\framework\extend\Redis;
use LiPhp\LiComm;
use App\structure\TaskData;

class HttpRequest extends AppBase
{
    protected ?\Swoole\Http\Request $request;
    protected $server;
    protected string $request_method = '';
    protected int $fd;
    protected array $postData = [];
    protected Response $response_handle;
    protected array $response_data = [];
    public bool $exitFlag = false;

    use \App\traits\crypt;

    public function __construct(?\Swoole\Http\Request $request=null, $server = null)
    {
        parent::__construct();
        if(!is_null($request)){
            $this->request = $request;
        }
        if(!is_null($server)){
            $this->server = $server;
        }
        $this->response_handle = new Response(['return_data'=>true]);

    }

    /**
     * Api请求日志记录表，生产环境建议单独记录（如mongodb库）以减少主库压力
     * @return bool|int
     */
    protected function apiLog()
    {
        if (DT_DEBUG){
            $log = [
                'url'      => $this->request->server['request_uri'],
                'params'   => json_encode($this->request->get, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'postdata' => $this->request->getContent(),
                'ip'       => $this->request->server['remote_addr'],
                'addtime'  => time(),
            ];
            return $this->db->table('api_request_log')->insert($log);
        }else{
            return 0;
        }
    }

    /**
     * 解密验签数据
     */
    protected function initialize()
    {
        if($this->postData){
            $check = $this->verifySign($this->postData, false);  //如果需要安全验证，要求必须有签名才可以请求，把perforce参数改为true
            if($check === false){
                $this->error(405, '数据验签失败！', ['request'=>$this->postData]);
                return;
            }
        }

        /* ---------------
        其它鉴权等
        //--------------*/
    }

    protected function init_request_data(?\Swoole\Http\Request $request=null)
    {
        if(!is_null($request)){
            $this->request = $request;
        }
        $this->fd = $this->request->fd;
        $this->request_method = $this->request->server['request_method'];
        /*----
        $req['header'] = $this->request->header;
        $req['get'] = $this->request->get;
        $req['post'] = $this->request->post;
        $req['files'] = $this->request->files;
        $req['cookie'] = $this->request->cookie;
        $req['content'] = $this->request->getContent();
        ----*/
        $_content = $this->request->getContent();

        $this->postData = $_content ? (json_decode($_content, true)??[]) : [];
        if(isset($this->postData['signType'])){
            $this->response_options(['signType'=>$this->postData['signType']]);
        }
        $this->DT_IP = $this->request->server['remote_addr'];

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
        $this->exitFlag = true;
        $this->response_data = $this->response_handle->success($data, $errcode, $msg);
        return $this->response_data;
    }

    public function error(int $errcode = 0, string $msg = 'fail', array $data = ['void'=>null])
    {
        $this->exitFlag = true;
        $this->response_data = $this->response_handle->error($errcode, $msg, $data);
        return $this->response_data;
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

    public function response_data(): array
    {
        return $this->response_data;
    }

    /**
     * 接口方法运行入口
     * @return mixed|null
     */
    final public function run()
    {
        $this->apiLog();
        $requestPath = isset($this->request->server['path_info']) ? explode('/',$this->request->server['path_info']) : [];
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
        $newClass = "App\\controller\\Http\\" . $action;
        try{
            $filename = DT_ROOT. '/app/controller/Http/';
            $filename .= str_replace("\\", '/', $action) . '.php';
            if(file_exists($filename)){

                $api = new $newClass();
                $api->server = $this->server;
                $api->init_request_data($this->request);
                if($api->exitFlag) return $api;
                if($action != 'index' && $func != 'authorize'){
                    $api->initialize();
                    if($api->exitFlag) return $api;
                }
                $api->$func();

                return $api;

            }else{
                swoole_error_log(5, "Http controller not found! {$newClass} {$func}");
                $this->error(404,'接口不存在！');
            }

        }catch(\Throwable $e){
            $emsg = $e->getMessage();
            if(DT_DEBUG){
                $data = ['request'=>$this->postData, 'exception'=>$e, 'code'=>$e->getCode(),'message'=>$e->getMessage(), 'trace'=>$e->getTrace()];
            }else{
                $data = ['code'=>$e->getCode(),'message'=>$e->getMessage()];
            }
            swoole_error_log(5, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->error(417, $emsg, $data);
        }

    }

    public function task(string $uri, mixed $data)
    {
        $data = new TaskData($uri, $data);
        return $this->server->task($data);
    }

    public function __call($name, $arguments) {
        //方法名$name区分大小写

        return $this->error(400, "调用方法：{$name} 不存在");
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
                $_str = serialize(['token'=>$token, 'secret'=>$secret]);
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
        $_str = serialize(['token'=>$token, 'secret'=>$secret]);
        Redis::set("ACCESS_".$appid, $_str, 7200);
        return [
            'suc'          => true,
            'access_token' => $token,
            'expires_in'   => $exp,
            'secret'       => $secret,
        ];
    }

}