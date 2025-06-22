<?php

namespace App\framework\extend;

/**
 * @method static \LiPhp\mysqli table(string $table, ?string $alias= null)
 */
class Db extends \LiPhp\Db
{
    /**
     * 构造方法
     * @access public $i 为配置文件db列表里的第几个配置
     */
    public static function Create(array $config, int $i=0, bool $new = false)
    {
        $cfg = $config['connections'][$i];
        $dbt = strtolower($cfg['database']);
        $db = match ($dbt) {
            'mysqli'    => new MySQLi($cfg),
            'sqlsrv'    => new SqlSrv($cfg),
            'mongodb'   => new MongoDB($cfg),
            'pgsql'     => new PgSql($cfg),
            default     => false,
        };
        if(!$new) static::$db = $db;
        return $db;
    }
}