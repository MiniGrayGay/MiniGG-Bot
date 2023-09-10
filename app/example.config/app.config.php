<?php

/**
 *
 * debug，会输出到 /app/cache/debug
 *
 */
define('APP_DEBUG', false);

/**
 *
 * 当前时间戳
 *
 */
$t = time();
define('TIME_T', $t);

/**
 *
 * send.php 主动推送的密钥
 *
 */
$appKey = array(
    "65e4f038e9857ceb12d481fb58e1e23d", //我
);
define('APP_KEY', $appKey);

/**
 *
 * 机器人信息
 *
 */
$botInfo = array(
    "XIAOAI" => array(
        "id" => "12345",
        "name" => "小爱同学",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => "12345"
    ),
    "MYPCQQ" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    ),
    "MYS" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    ),
    "OPQ" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    ),
    "WSLY" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    ),
    "NOKNOK" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => "",
        "oper_id" => ""
    ),
    "QQGroup" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    ),
    "QQGuild" => array(
        array(
            "id" => "",
            "name" => "",
            "accessToken" => "",
            "verifyToken" => "",
            "uin" => ""
        ),
        array(
            "id" => "",
            "name" => "",
            "accessToken" => "",
            "verifyToken" => "",
            "uin" => ""
        )
    ),
    "XXQ" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    )
);
define('APP_BOT_INFO', $botInfo);

/**
 * 
 * 短域名
 * 
 */
define('APP_BOT_SHORT_URL', "91m.top");

/**
 *
 * 参数信息
 *
 */
define('APP_BOT_TYPE', $_GET['botType'] ?? 1); //1 公域，0 私域
define('FRAME_ID', $_GET['frameId'] ?? 50000);
define('FRAME_HOST', $_GET['frameHost'] ?? "127.0.0.1:1111");
define('FRAME_GC', $_GET['frameGc'] ?? NULL);
define('FRAME_KEY', $_POST['key'] ?? NULL);

/**
 *
 * send.php 主动推送的参数
 *
 */
define('PUSH_MSG_ROBOT', $_POST['msgRobot'] ?? 0);
define('PUSH_MSG_TYPE', $_POST['msgType'] ?? 1);
define('PUSH_MSG_SOURCE', $_POST['msgSource'] ?? 0);
define('PUSH_MSG_SENDER', $_POST['msgSender'] ?? 0);
define('PUSH_MSG_CONTENT', $_POST['msgContent'] ?? NULL);
$msgExt = $_POST['msgExt'] ?? NULL;
define('PUSH_MSG_EXT', $msgExt ? json_decode($msgExt, true) : array());

/**
 *
 * 框架的回调地址
 *
 */
$originInfo[10000] = "http://127.0.0.1:8010";
$originInfo[60000] = "http://127.0.0.1:8020";
$originInfo[20000] = "http://127.0.0.1:8073";
$originInfo[15000] = "http://127.0.0.1:8086";
$originInfo[50000] = "https://openapi.noknok.cn";
$originInfo[70000] = "https://api.sgroup.qq.com";
$originInfo[75000] = "https://api.sgroup.qq.com";
$originInfo[80000] = "https://api.91m.top";
$originInfo[90000] = "https://bbs-api.miyoushe.com";

$appOrigin = $originInfo[FRAME_ID] ?? NULL;
if (in_array(FRAME_ID, array(10000, 15000, 20000, 60000))) {
    $appOrigin = str_replace($appOrigin, "http://" . FRAME_HOST, $appOrigin);
}
define('APP_ORIGIN', $appOrigin);

/**
 *
 * debug 输出格式
 *
 */
function appDebug($type, $log)
{
    //if (!APP_DEBUG) return;

    $dir = APP_DIR_CACHE . "debug";

    /**
     *
     * 不存在自动创建文件夹
     *
     */
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents("{$dir}/{$type}_" . FRAME_ID . "_" . TIME_T . ".txt", $log);
}