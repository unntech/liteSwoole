<?php
declare (strict_types = 1);

use LiPhp\Config;

defined('IN_LitePhp') or exit('Access Denied');

function config(string $name = null, $default = null){
    if (str_contains($name, '.')) {
        $v = explode('.',$name);
        $key = $v[0];
    }else{
        $key = $name;
    }
    if(!Config::exists($key)){
        Config::load($key);
    }
    return Config::get($name, $default);
}

function exception_handler(Throwable $e): void
{
    if (defined('DT_DEBUG') && DT_DEBUG) {
        $postDate = json_decode(file_get_contents("php://input"), true);
        $data = ['request'=>$postDate, 'exception'=>$e, 'code'=>$e->getCode(),'message'=>$e->getMessage(), 'trace'=>$e->getTrace()];
    }else{
        $data = ['code'=>$e->getCode(),'message'=>$e->getMessage()];
    }
    echo json_encode($data) . PHP_EOL;
}