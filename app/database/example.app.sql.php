<?php

define('SQL_PREFIX', "susu_");
//数据库前缀

/**
 *
 * 数据库配置
 *
 */
$dbConfig = array(
    array("localhost", "root", "test", "dbname", 9999)
);
define('APP_DB_CONFIG', $dbConfig);

/**
 *
 * Redis 配置
 *
 */
$redisConfig = array(
    array("127.0.0.1", "", 6379)
);
define('APP_REDIS_CONFIG', $redisConfig);
