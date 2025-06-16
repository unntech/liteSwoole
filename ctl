SERVICE_NAME=LiteSwoole
# 查找进程ID
#CTL_PID=$(pgrep -x LiteSwoole | grep -v "$$")
if [ -f "app.pid" ]; then
    CTL_PID=$(cat "app.pid")
else
    CTL_PID=
fi

function wait_stop_service() {
    local pid
    for i in {1..10}
    do
      if ps -p "$CTL_PID" > /dev/null 2>&1; then
        printf 'Wait:\t%s\r' "$i"
        sleep 1
      else
        return
      fi
    done
    kill -9 $CTL_PID
    sleep 2
    return
}

case $1 in
    start)
        echo "Starting $SERVICE_NAME service..."
        # 启动服务的命令
        php app.php
        echo "Service $SERVICE_NAME has been started."
        ;;
    stop)
        if [ -n "$CTL_PID" ]; then
          echo "Stopping service..."
          # 停止服务的命令
          kill -15 $CTL_PID
          wait_stop_service
          echo "Service stopped."
        else
          echo "Service $SERVICE_NAME is not running."
        fi
        ;;
    restart)
        echo "Restarting $SERVICE_NAME service..."
        # 重启服务的命令
        # 检查进程是否正在运行
        if [ -z "$CTL_PID" ]; then
            echo "Service $SERVICE_NAME is not running."
        else
            # 终止进程
            echo "Stopping $SERVICE_NAME Service..."
            kill -15 $CTL_PID
            wait_stop_service
            echo "$SERVICE_NAME stopped."
        fi
        # 重新启动服务
        echo "Starting $SERVICE_NAME service..."
        php app.php
        echo "Service $SERVICE_NAME has been started."
        ;;
    status)
        # 检查进程是否正在运行
        if [ -n "$CTL_PID" ]; then
            ps $CTL_PID
        else
            echo "$SERVICE_NAME is not running."
        fi
        ;;
    *)
        echo "Invalid parameter. Valid parameter are: start, stop, restart, status."
        ;;
esac
