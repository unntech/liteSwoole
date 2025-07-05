<?php

namespace App\controller;

use App\framework\WebSocket;
use LiPhp\PgSql;

class WebSocketOpen extends WebSocket
{
    public function __construct($server = null)
    {
        parent::__construct($server);
    }

    public function open(\Swoole\Http\Request $request)
    {
        // 可根据实际情况验证如 $request->get['appid'], $request->get['secret'] 或 access_token 判断是否可以连接
        //$this->server->close($request->fd);  //验证不通过的，关闭连接

        $this->db->table('ws_clients');
        if($this->db instanceof PgSql){
            $this->db->returning('fd');
        }
        $this->db->insert([
            'fd'          => $request->fd,
            'addtime'     => time(),
            'remote_addr' => $request->server['remote_addr'],
            'sec_key'     => $request->header['sec-websocket-key'],
        ]);

    }
}