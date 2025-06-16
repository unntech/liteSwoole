<?php

namespace App\controller;

use App\framework\extend\Redis;
use App\framework\WebSocket;

class WebSocketAuthorize extends WebSocket
{
    protected int|string $appid;

    public function __construct()
    {
        parent::__construct();
    }

    public function initialize()
    {
        /*  //如果需要安全验证，要求必须有签名才可以请求，如果ApiBase里init_request_data已经启用，那这里就不要重复验证
        if(!isset($this->postData['signType']) || !in_array($this->postData['signType'], ['MD5', 'SHA256', 'RSA', 'ECDSA'])){
            $this->error(400, '无请求数据或无效 signType！', ['request'=>$this->postData]);
        }
        //*/

        //验证接口权限等初始化过程
        $jwt = $this->verifyToken($this->data['head']['access_token'] ?? '');
        if($jwt === false){
            $this->error(401, 'Unauthorized');
            return;
        }
        //增加安全性可配合签发 access_token 时同步写入 Redis缓存，这里对应读出验证合法

        if(empty($jwt['sub'])){
            $this->error(401, 'Unauthorized appid');
            return;
        }
        $this->appid = $jwt['sub'] ?? 0;

        //*----  从签发时保存的对应客户端的 Redis缓存获取对应的secret值示例，按生产环境自已的流程设计
        $_s = Redis::get("ACCESS_".$this->appid);
        if($_s){
            $s = unserialize($_s);
            if($s['token'] == $this->data['head']['access_token'] && $s['fd'] == $this->fd){
                $this->response_options(['secret'=>$s['secret']]);
            }else{
                $this->error(401, 'access_token expired or not current fd');
                return;
            }
        }else{
            $this->error(401, 'access_token expired');
            return;
        }
        //----------------*/

        parent::initialize();
        if($this->exitFlag) return;

        /* --- 等等其它鉴权不成功则退出
        $notAllow = true;
        if($notAllow){
            $this->error(401, 'Unauthorized');
        }
        */
    }
}