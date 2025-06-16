<?php

namespace App\controller\WebSocket;

use App\controller\WebSocketAuthorize;

class sampleAuthorize extends WebSocketAuthorize
{
    public function __construct()
    {
        parent::__construct();
    }

    //请求处理函数，按需添加编写
    public function test()
    {
        $data = [
            'title'=>'This is a testing.',
            'data' => $this->data,
            'appid'   => $this->appid,
        ];

        $this->success($data,0, "调用Authorize方法: test 成功");
    }
}