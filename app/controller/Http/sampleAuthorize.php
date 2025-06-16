<?php

namespace App\controller\Http;

use App\controller\HttpAuthorizeRequest;

class sampleAuthorize extends HttpAuthorizeRequest
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
            'IP'=>$this->DT_IP,
            'postData' => $this->postData,
            'appid'   => $this->appid,
        ];

        $this->success($data,0, "调用Authorize方法: test 成功");
    }
}