<?php

namespace App\structure;

class TaskData
{
    /**
     * webSocket 在线的的fd
     * @var int
     */
    public readonly int $fd;
    /**
     * 处理路径，用于调度不同任务程序执行
     * @var string
     */
    public readonly string $uri;
    /**
     * 接收的数据
     * @var mixed
     */
    public readonly mixed $data;
    /**
     * 处理存放的数据
     * @var mixed
     */
    public mixed $record;
    /**
     * 处理进程状态 0:创建; 1:处理中; 2:异常; 3:处理完成; 9:完结
     * 状态为9:完结， 则不继续调用 onTaskFinish 程序
     * @var int
     */
    public int $status = 0;

    public function __construct(string $uri, mixed $data, int $fd = 0)
    {
        $this->uri = (str_starts_with($uri, '/') ? $uri : '/' . $uri);
        $this->data = $data;
        $this->fd = $fd;
    }

    /**
     * 设置或获取状态值
     * @param int|null $status
     * @return int
     */
    public function status(?int $status = null): int
    {
        if(!is_null($status)){
            $this->status = $status;
        }
        return $this->status;
    }
}