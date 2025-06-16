<?php

namespace App\controller;

use App\framework\WebSocket;

class WebSocketClose extends WebSocket
{
    public function __construct($server = null)
    {
        parent::__construct($server);
    }

    /**
     * @param int $fd
     * @param int $reactorId  来自哪个 reactor 线程，服务器主动 close 关闭时为负数
     * @return void
     */
    public function close(int $fd, int $reactorId)
    {
        $this->db->table('ws_clients')->where(['fd' => $fd])->delete();
    }
}