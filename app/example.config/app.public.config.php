<?php

/**
 *
 * 微信邀请加群
 *
 */
$inviteInGroup = array(
    "12345@chatroom"
);
$appInfo['inviteInGroup'] = $inviteInGroup;

/**
 *
 * 存放一些 api 密钥之类的
 *
 */
$authInfo[1000] = array(
    ""
);
//-
$appInfo['authInfo'] = $authInfo;

/**
 *
 * 各个框架相同表情的实现
 *
 */
$iconInfo[0] = array(
    '🔥',
    '✨',
    '😅'
);
$iconInfo[10000] = array(
    '\uF09F94A5',
    '\uE29CA8',
    '\uF09F9885'
);
$iconInfo[20000] = array(
    '[@emoji=\uD83D\uDD25]',
    '[@emoji=\u2728]',
    '[@emoji=\uD83D\uDE05]'
);
//-
$appInfo['iconInfo'] = $iconInfo;

/**
 *
 * 省份列表
 *
 */
$appInfo['provinceType'] = array(
    "请选择省份", //0
    "安徽省", //1
    "澳门特别行政区", //2
    "北京市", //3
    "重庆市", //4
    "福建省", //5
    "甘肃省", //6
    "广东省", //7
    "广西壮族自治区", //8
    "贵州省", //9
    "海南省", //10
    "河北省", //11
    "河南省", //12
    "黑龙江省", //13
    "湖北省", //14
    "湖南省", //15
    "吉林省", //16
    "江苏省", //17
    "江西省", //18
    "辽宁省", //19
    "内蒙古自治区", //20
    "宁夏回族自治区", //21
    "青海省", //22
    "山东省", //23
    "山西省", //24
    "陕西省", //25
    "上海市", //26
    "四川省", //27
    "台湾省", //28
    "天津市", //29
    "西藏自治区", //30
    "香港特别行政区", //31
    "新疆维吾尔自治区", //32
    "云南省", //33
    "浙江省" //34
);

/**
 *
 * 大区列表
 *
 */
$appInfo['areaType'] = array(
    "请选择大区", //0
    "安卓QQ", //1
    "苹果QQ", //2
    "安卓WX", //3
    "苹果WX" //4
);

FRAME_ID != 20000 ? $nowAreaType = "安卓QQ" : $nowAreaType = "安卓WX";
$appInfo['nowAreaType'] = $nowAreaType;
//默认大区

/**
 *
 * 报错码
 *
 */
$codeInfo[1000] = "非白名单";
$codeInfo[1001] = "该群 或 框架暂不支持该功能";
$codeInfo[1002] = "内容为空，请稍后再来看看吧";
$codeInfo[1003] = "还未更新，请稍后再来看看吧";
$codeInfo[1004] = "玩家不存在 或 未公开";
$codeInfo[1005] = "可能存在违规内容，请修改后再试试吧~";
//-
$appInfo['codeInfo'] = $codeInfo;

/**
 *
 * 白名单群号
 *
 */
$whiteListInfo['coser'] = array();
$whiteListInfo['winRate'] = array();
//-
$appInfo['whiteListInfo'] = $whiteListInfo;

/**
 *
 * 特殊群，列表内的将不触发插件
 *
 */
$specialGroup = array();
define('APP_SPECIAL_GROUP', $specialGroup);

/**
 *
 * 常用 api
 *
 */
define('APP_API_HOST', "http://api.91m.top");
define('APP_API_APP', APP_API_HOST . "/hero/v1/app.php");
define('APP_API_GAME', APP_API_HOST . "/hero/v1/game.php");
define('APP_API_ROBOT', APP_API_HOST . "/hero/v1/robot.php");
define('APP_API_VERCEL', "https://efd77fa25b8bb282.vercel.app");
define('APP_API_MINIGG', "https://info.minigg.cn/");
//-
define('APP_CD', 5);
define('APP_HOME', "https://pvp.91m.top");
define('APP_PROXY_IMG', "https://91m.top/p?url=");
define('APP_NO_KEYWORDS', "指令不对哦，是要找咱玩嘛~\n发送【功能】可以查看咱的所有技能!");
define('APP_WECHAT_TOPIC', "欢迎关注 #苏苏的荣耀助手");

/**
 *
 * 小灰灰的原神 api
 *
 */
$miniGGInfo['Api'] = APP_API_MINIGG;
$miniGGInfo['GachaSet'] = "https://bot.q.minigg.cn/src/plugins/genshingacha/set.php";
$miniGGInfo['Characters'] = APP_API_MINIGG . "characters?query=";
$miniGGInfo['Weapons'] = APP_API_MINIGG . "weapons?query=";
$miniGGInfo['Talents'] = APP_API_MINIGG . "talents?query=";
$miniGGInfo['Constellations'] = APP_API_MINIGG . "constellations?query=";
$miniGGInfo['Foods'] = APP_API_MINIGG . "foods?query=";
$miniGGInfo['Enemies'] = APP_API_MINIGG . "enemies?query=";
$miniGGInfo['Domains'] = APP_API_MINIGG . "domains?query=";
$miniGGInfo['Artifacts'] = APP_API_MINIGG . "artifacts?query=";
//-
$appInfo['miniGG'] = $miniGGInfo;

/**
 *
 * 卡片信息
 *
 */
define('APP_DESC', "新闻");
define('APP_MSG_ID', 1105200115);
define('APP_MSG_NAME', "com.tencent.structmsg");
define('APP_MSG_TAG', "苏苏的荣耀助手");
define('APP_MSG_TYPE', 1);
define('APP_VIEW', "news");

define('APP_INFO', $appInfo);