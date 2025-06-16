<?php

namespace App\framework;

use LiPhp\Config;
use App\framework\extend\Db;
use App\framework\extend\Redis;

class LiApp
{
    const VERSION = '2.0.1';
    /**
     * @var extend\MySQLi
     */
    public static $db;
    /**
     * @var \Redis
     */
    public static $redis;
    /**
     * @var \Swoole\Table
     */
    public static $commTable;
    public static int $DT_TIME;
    public static string $appName;
    public static string $domain;

    public static function initialize(): void
    {
        self::$DT_TIME = time();

        Config::initialize(DT_ROOT . "/config/");
        Config::load(['app', 'db', 'redis', 'swoole']);
        self::$appName =Config::get('app.name', 'LiteSwoole');
        self::$domain =Config::get('app.domain', '');
        define('ENVIRONMENT', Config::get('app.ENVIRONMENT', 'DEV'));
        define('DT_DEBUG', Config::get('app.APP_DEBUG', true));
        if (DT_DEBUG) {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_ERROR);
        }
        define('APP_VERSION', Config::get('app.version', self::VERSION));
        define('DT_KEY', Config::get('app.authkey', 'LitePhp'));
    }

    /**
     * 连接数据库
     * @param int $i 为配置文件db列表里的第几个配置
     * @return void
     */
    public static function set_db(int $i = 0): void
    {
        self::$db = Db::Create(Config::get('db'), $i);
    }

    /**
     * 连接一个新的数据库
     * @param int $i 为配置文件db列表里的第几个配置
     * @return false|extend\MySQLi|extend\SqlSrv|extend\MongoDB
     */
    public static function new_db(int $i = 0)
    {
        return Db::Create(Config::get('db'), $i, true);
    }

    /**
     * 连接Redis
     * @param bool $reconnect 重连
     * @return void
     */
    public static function set_redis(bool $reconnect = false): void
    {
        if(empty(self::$redis) || $reconnect) {
            self::$redis = Redis::Create(Config::get('redis'));
        }
    }

    public static function alog(string $type, ?string $log1='', ?string $log2 = '', ?string $log3 = '' )
    {
        return self::$db->table('alog')->insert([
            'type' => $type,
            'log1' => $log1,
            'log2' => $log2,
            'log3' => $log3
        ]);
    }

    public static function worker_begin(int $db_i = 0): void
    {
        self::set_db($db_i);
        \LiPhp\Model::setDb(self::$db);
        self::set_redis();
    }

    public static function worker_end(int $db_i = 0): void
    {
        self::$db->close();
        Redis::$redis->close();
    }

}