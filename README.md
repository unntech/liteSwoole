liteSwoole 2.0
===============

[![Latest Stable Version](https://poser.pugx.org/unntech/liteswoole/v/stable)](https://packagist.org/packages/unntech/liteswoole)
[![Total Downloads](https://poser.pugx.org/unntech/liteswoole/downloads)](https://packagist.org/packages/unntech/liteswoole)
[![Latest Unstable Version](http://poser.pugx.org/unntech/liteswoole/v/unstable)](https://packagist.org/packages/unntech/liteswoole)
[![PHP Version Require](http://poser.pugx.org/unntech/liteswoole/require/php)](https://packagist.org/packages/unntech/liteswoole)
[![License](https://poser.pugx.org/unntech/liteswoole/license)](https://packagist.org/packages/unntech/liteswoole)

基于PHP Swoole 创建的协程框架，可用于生产环境的高性能API接口 和 WebSocket 连接。

## 主要新特性
* 最为接近原生PHP写法，满足习惯原生开发的人员开发习惯
* 采用`PHP8`强类型（严格模式）
* 支持更多的`PSR`规范
* 原生多应用支持
* 对Swoole以及协程支持
* 对IDE更加友好
* 统一和精简大量用法


> liteSwoole 2.0的运行环境要求PHP8.1+
> 需要安装 ext-swoole 扩展

## 安装

~~~
composer create-project unntech/liteswoole yourApp
~~~

~~~
将目录config.sample 改名为 config，可以更据需求增加配置文件
读取例子见：tests/sample.config.php
docs/liteswoole.sql 导入至数据库
~~~


启动服务，可在 config/swoole.php `'services' => ['http', 'webSocket', 'task']` 配置需要启动的服务

~~~
cd yourApp
./ctl start    #chmod +x ctl 先赋予可执行权限
~~~

### Http 接口访问

~~~
http://localhost:9898/authorize  #获取TOKEN

http://localhost:9898/index/index
~~~

~~~
访问的路径对应/app/controller/Http/文件名/函数名
~~~


### websocket连接
~~~
ws://localhost:9898
~~~

~~~
/app/controller/WebSocketOpen.php       # 处理连接事件
/app/controller/WebSocketClose.php      # 处理断线事件
/app/controller/WebSocket/文件名/函数名   # 按message里 head.uri 的路径对应解析
~~~

如果需要更新框架使用
~~~
composer update unntech/liphp
~~~

目录结构
~~~
yourApp/
├── app                                     #App命名空间
│   ├── controller                          #控制器方法目录
│   │   ├── Http                            #接口控制器目录，支持分项多级子目录
│   │   ├── WebSocket                       #WebSocket消息处理，支持分项多级子目录
│   │   ├── BootStrap.php                   #服务启动事件处理程序
│   │   ├── HttpAuthorizeRequest.php        #带验证access_token的Http请求父类
│   │   ├── WebSocketClose.php              #WebSocket Close处理类
│   │   └── WebSocketOpen.php               #WebSocket Open处理类
│   ├── framework                           #框架核心基础文件
│   │   ├── extend                          #继承vendor框架类，供扩展和重写方法
│   │   ├── AppBase.php                     #app基础父类
│   │   ├── HttpRequest.php                 #Http请求基础类
│   │   ├── LiApp.php                       #App通用类，入口初始化数据
│   │   ├── ModelBase.php                   #模型基础类
│   │   ├── Response.php                    #API 标准输出类
│   │   └── WebSocket.php                   #WebSocket请求基础类
│   ├── model                               #模型类
│   ├── structure                           #自定义的数据结构体
│   ├── traits
│   ├── ...                                 #其它子模块
├── config                                  #配置文件
│   ├── app.php                             #项目基础配置
│   ├── db.php                              #数据库配置文件
│   ├── redis.php                           #redis配置文件
│   ├── swoole.php                          #Http/WebSocket配置文件
├── docs                                    #文档
│   ├── liteswoole.sql.gz                   #LiteSwoole模块数据库
├── include                                 #通用函数库
│   ├── common.php                          #全局通用函数
├── log                                     #日志存放目录
├── app.php                                 #服务启动主程序
├── autoload.php                            #autoload载入主程序
├── CHANGELOG.md                            #版本更新日志
├── composer.json                           #
└── README.md
~~~

## 命名规范

`LiteSwoole`遵循PSR命名规范和PSR-4自动加载规范。

## 参与开发

直接提交PR或者Issue即可  
> [版本更新记录 CHANGELOG](CHANGELOG.md)

## 版权信息

liteSwoole遵循MIT开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2025 by Jason Lin All rights reserved。