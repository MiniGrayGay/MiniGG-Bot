<?php

require_once(__DIR__ . "/vendor/autoload.php");

ini_set("display_errors", "off");
//显示错误提示 on:打开 off:关闭
//ini_set("error_reporting", E_ALL);
//显示所有错误

/**
 *
 * 时区配置
 *
 */
ini_set("date.timezone", "Asia/Shanghai");
ini_set("max_execution_time", 0);

/**
 *
 * 接口 User-Agent 格式
 *
 */
header("Access-Control-Allow-Origin: *");
header("Content-type: application/json; charset=utf-8");

/**
 *
 * 引用模块
 *
 */
$dir = dirname(__FILE__);

define('APP_DIR_CONFIG', "{$dir}/app/config/");
require_once(APP_DIR_CONFIG . "app.config.php");
require_once(APP_DIR_CONFIG . "app.public.config.php");
require_once(APP_DIR_CONFIG . "app.definition.php");

define('APP_DIR_DATABASE', "{$dir}/app/database/");
require_once(APP_DIR_DATABASE . "app.sql.php");

define('APP_DIR_CLASS', "{$dir}/app/class/");
require_once(APP_DIR_CLASS . "api.class.php");
require_once(APP_DIR_CLASS . "app.class.php");

define('APP_DIR_CACHE', "{$dir}/app/cache/");
define('APP_DIR_PLUGINS', "{$dir}/app/plugins/");
