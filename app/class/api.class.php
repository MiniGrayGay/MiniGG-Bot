<?php

use Onekb\ChatGpt\ChatGpt;
use Overtrue\Pinyin\Pinyin;

class api
{
    public $redis;

    public $chatGPT;

    public $pinyin;

    /**
     *
     * XIAOAI:返回请求API的结果
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段，详见 send.php 示例
     *
     * @link https://developers.xiaoai.mi.com
     */
    public function requestApiByXIAOAI($newMsg, $msgExtArr = array())
    {
        /*
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        */
        $msgOrigMsg = $msgExtArr['msgOrigMsg'];
        $extMsgType = $msgExtArr['msgType'];

        $reqRet = json_encode(
            array(
                'version' => '1.0',
                'session_sttributes' => array(),
                'response' => array(
                    'open_mic' => true,
                    'to_speak' => array(
                        'type' => 0,
                        'text' => $newMsg
                    )
                ),
                'is_session_end' => false
            )
        );

        echo $reqRet;

        if (APP_DEBUG)
            appDebug("output", $newMsg . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * MPQ:返回请求API的结果
     *
     * @param string $newMsg 回复内容
     *
     * @link https://www.yuque.com/mpq/docs
     */
    public function requestApiByMPQ($newMsg)
    {
        $reqUrl = APP_ORIGIN . "/?API=" . urlencode($newMsg);

        /*
        $reqUrl = str_replace("%3C", "<", $reqUrl);
        $reqUrl = str_replace("%3E", ">", $reqUrl);
        $reqUrl = str_replace("%27", "'", $reqUrl);
        $reqUrl = str_replace("%27", "'", $reqUrl);
        $reqUrl = str_replace("%28", "(", $reqUrl);
        $reqUrl = str_replace("%29", ")", $reqUrl);
        $reqUrl = str_replace("%5b", "[", $reqUrl);
        $reqUrl = str_replace("%5d", "]", $reqUrl);
        $reqUrl = str_replace("%40", "@", $reqUrl);
        $reqUrl = str_replace("%3D", "=", $reqUrl);
        $reqUrl = str_replace("%3A", ":", $reqUrl);
        $reqUrl = str_replace("%2F", "/", $reqUrl);
        $reqUrl = str_replace("%2C", ",", $reqUrl);
        $reqUrl = str_replace("%3F", "?", $reqUrl);
        */
        $reqUrl = str_replace("+", "%20", $reqUrl);

        $reqRet = $this->requestUrl($reqUrl);
        $resJson = json_decode($reqRet);
        $resData = base64_decode($resJson->Data);

        if (APP_DEBUG)
            appDebug("output", $reqUrl . "\n\n" . $reqRet);

        return $resData;
    }

    /**
     * 
     * OPQ:返回请求API的结果
     *
     * @param string $newMsg 回复内容
     *
     * @link https://73s2swxb4k.apifox.cn
     */
    public function requestApiByOPQ($newMsg, $msgExtArr = array())
    {
        /*
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        */
        $msgOrigMsg = $msgExtArr['msgOrigMsg'];
        $extMsgType = $msgExtArr['msgType'];

        $msgEventData = $msgOrigMsg['CurrentPacket']['EventData'];

        $msgRobot = $msgOrigMsg['CurrentQQ'] ?? 0;
        $msgType = $msgEventData['MsgHead']['FromType'] ?? 0;
        $msgSource = $msgEventData['MsgHead']['FromUin'] ?? 0;
        $msgSender = $msgEventData['MsgHead']['SenderUin'] ?? 0;

        $reqUrl = APP_ORIGIN . "/v1/LuaApiCaller?funcname=MagicCgiCmd&timeout=10&qq={$msgRobot}";

        $postArr = array(
            "CgiCmd" => "MessageSvc.PbSendMsg",
            "CgiRequest" => array(
                "ToUin" => (int) $msgSource,
                "ToType" => (int) $msgType,
                "Content" => $newMsg
            )
        );

        if (strpos($extMsgType, "at_msg") > -1) {
            $postArr['CgiRequest']['AtUinLists'][] = array(
                "Nick" => "我",
                "Uin" => $msgSender
            );
        }

        $botInfo = APP_BOT_INFO['OPQ'];
        $postArr['key'] = $botInfo['accessToken'];
        $postData = json_encode($postArr);

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            array(
                "Content-Type: application/json"
            )
        );

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * 可爱猫:未死鲤鱼:返回请求API的结果
     *
     * 支持类型 at_msg、json_msg
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     *
     * @link http://www.keaimao.com.cn/forum.php
     */
    public function requestApiByWSLY($newMsg, $msgExtArr = array())
    {
        $reqUrl = APP_ORIGIN . "/send";

        /*
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        */
        $msgOrigMsg = $msgExtArr['msgOrigMsg'];
        $extMsgType = $msgExtArr['msgType'];

        $msgRobot = $msgOrigMsg['robot_wxid'] ?? NULL;
        $msgSource = $msgOrigMsg['from_wxid'] ?? NULL;

        if ($extMsgType == "json_msg") {
            $postArr = $msgOrigMsg;
        } else {
            if ($extMsgType == "at_msg") {
                $sendType = 102;

                $msgAtWxid = $msgOrigMsg['final_from_wxid'];
                $msgAtName = urldecode($msgOrigMsg['final_from_name']);
            } else {
                $sendType = 100;

                $msgAtWxid = NULL;
                $msgAtName = NULL;
            }

            $postArr = array();
            $postArr['type'] = $sendType;
            $postArr['robot_wxid'] = $msgRobot;
            $postArr['to_wxid'] = $msgSource;
            $postArr['at_name'] = $msgAtName;
            $postArr['at_wxid'] = $msgAtWxid;
            $postArr['msg'] = urlencode($newMsg);
        }

        $botInfo = APP_BOT_INFO['WSLY'];
        $postArr['key'] = $botInfo['accessToken'];
        $postData = json_encode(
            array(
                "data" => json_encode($postArr, JSON_UNESCAPED_UNICODE)
            )
        );

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            array(
                "Content-Type: application/json"
            )
        );

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * NOKNOK:返回请求API的结果
     *
     * 支持类型 at_msg、image_msg、markdown_msg、reply_msg
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     *
     * @link https://bot-docs.github.io/pages/events/1_callback.html
     */
    public function requestApiByNOKNOK($newMsg, $msgExtArr = array())
    {
        $reqUrl = APP_ORIGIN . "/api/v1/SendGroupMessage";
        //$reportUrl = APP_ORIGIN . "/api/v1/CommReport";

        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
            $msgOrigMsg = $newData['msgOrigMsg']['data'][0];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
            $msgOrigMsg = $newData['msgOrigMsg'];
        }
        $extMsgType = $newData['msgType'];

        $msgGuildId = $msgOrigMsg['gid'] ?? 0;
        $msgChannelId = $msgOrigMsg['target_id'] ?? 0;
        $msgSenderUid = $msgOrigMsg['sender_uid'] ?? 0;
        $msgId = $msgOrigMsg['msg_id'] ?? 0;
        $msgTs = $msgOrigMsg['ts'] ?? 0;
        $msgNonce = $msgOrigMsg['nonce'] ?? NULL;

        $postBody = array(
            "content" => $newMsg,
        );

        $botInfo = APP_BOT_INFO['NOKNOK'];

        if ($extMsgType) {
            if ($extMsgType == "markdown_msg") {
                $l2_type = 8;
                $l3_types = array();

                $newMsg = str_replace("\n", "\n\n", $newMsg);
            } elseif ($extMsgType == "image_msg") {
                $l2_type = 3;
                $l3_types = array();

                $postBody = array(
                    "pic_info" => json_decode($newMsg)
                );
            } elseif ($extMsgType == "reply_msg") {
                $l2_type = 1;
                $l3_types = array(1);

                $msgContent = $msgOrigMsg['body']['content'] ?? NULL;
                $oldMsg = substr($msgContent, strpos($msgContent, ")") + 1, strlen($msgContent));

                $newExtData = array(
                    "content" => "@" . $botInfo['name'] . " " . $oldMsg,
                    "uid_replied" => $msgSenderUid,
                    "msg_seq" => explode("_", $msgId)[2],
                    "msg_id" => "",
                );
            } elseif ($extMsgType == "at_msg") {
                $l2_type = 1;
                $l3_types = array(3);

                $msgAtNokNok = $newData['msgAtNokNok'] ?? NULL;
                $msgAtNokNok ? $newExtData = $msgAtNokNok : $newExtData = array("at_type" => 1, "at_uid_list" => array($msgSenderUid));
            }

            if ($newExtData)
                $postBody[$extMsgType] = $newExtData;
        } else {
            $l2_type = 1;
            $l3_types = array();
        }

        $postArr = array(
            "gid" => $msgGuildId,
            "target_id" => $msgChannelId,
            "ts" => $msgTs,
            "nonce" => $msgNonce
        );

        $postArr['l2_type'] = $l2_type;
        $postArr['l3_types'] = $l3_types;
        $postArr['body'] = $postBody;
        $postData = json_encode($postArr);

        $postHeader[] = "Content-Type: application/json";
        $postHeader[] = "Authorization: " . $botInfo['accessToken'];

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            $postHeader
        );

        /*
        $reportArr = array(
        "ts" => $msgTs,
        "nonce" => $msgNonce,
        "data_list" => array(
        "oper_id" => $botInfo['oper_id'],
        "gid" => $msgGuildId,
        "target_id" => $msgChannelId,
        "to_uid" => $msgSenderUid,
        "scope" => "channel"
        )
        );
        $reportData = json_encode($reportArr);
        $reportRet = $this->requestUrl(
        $reportUrl,
        $reportData,
        array(
        "Content-Type: application/json",
        "Authorization: " . $botInfo['accessToken']
        )
        );
        */

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * QQ频道:返回请求API的结果
     *
     * 支持类型 at_msg、image_msg、json_msg、reply_msg、xml_msg
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     *
     * @link https://github.com/Mrs4s/go-cqhttp
     */
    public function requestApiByQQGuild_1($newMsg, $msgExtArr = array())
    {
        /*
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        */
        $msgOrigMsg = $msgExtArr['msgOrigMsg'];
        $extMsgType = $msgExtArr['msgType'];

        $msgType = $msgOrigMsg['message_type'] ?? NULL;
        $msgId = $msgOrigMsg['message_id'] ?? 0;
        $msgSender = $msgOrigMsg['sender']['user_id'] ?? 0;

        if ($msgType == "guild") {
            $msgGuildId = $msgOrigMsg['guild_id'] ?? 0;
            $msgChannelId = $msgOrigMsg['channel_id'] ?? 0;

            $reqUrl = APP_ORIGIN . "/send_guild_channel_msg";
        } else {
            $msgGroupId = $msgOrigMsg['group_id'] ?? 0;

            $reqUrl = APP_ORIGIN . "/send_msg";
        }

        $postMsg = [];

        if (strpos($extMsgType, "reply_msg") > -1) {
            $postMsg[] = array(
                "type" => "reply",
                "data" => array(
                    "id" => $msgId
                )
            );
        }

        if (strpos($extMsgType, "at_msg") > -1) {
            $msgAtGuild = $msgExtArr['msgAtQQGuild'] ?? array();
            $msgAtType = $msgAtGuild['at_type'];

            $postMsg[] = array(
                "type" => "at",
                "data" => array(
                    "qq" => $msgAtType == 2 ? "all" : $msgSender
                )
            );

            //$postMsg = array_reverse($postMsg);
            //at 靠前
        }

        //可以叠加

        if ($extMsgType == "json_msg") {
            $postMsg[] = array(
                "type" => "json",
                "data" => array(
                    "data" => $newMsg
                )
            );
        }

        if ($extMsgType == "xml_msg") {
            $postMsg[] = array(
                "type" => "xml",
                "data" => array(
                    "data" => $newMsg
                )
            );
        }

        if ($extMsgType == "record_msg") {
            $postMsg[] = array(
                "type" => "record",
                "data" => array(
                    "file" => "https://fanyi.baidu.com/gettts?lan=zh&text=" . urlencode($newMsg) . "&spd=5&source=web"
                )
            );
        }

        if ($extMsgType == "tts_msg") {
            $postMsg[] = array(
                "type" => "tts",
                "data" => array(
                    "text" => $newMsg
                )
            );
        }

        if ($newMsg) {
            $postMsg[] = array(
                "type" => "text",
                "data" => array(
                    "text" => $newMsg
                )
            );
        }

        if (strpos($extMsgType, "image_msg") > -1) {
            $extMsgImgUrl = $msgExtArr['msgImgUrl'] ?? NULL;

            $postMsg[] = array(
                "type" => "image",
                "data" => array(
                    "file" => $extMsgImgUrl
                )
            );
        }

        if ($msgType == "guild") {
            $postArr = array(
                "guild_id" => $msgGuildId,
                "channel_id" => $msgChannelId,
                "message" => $postMsg
            );
        } elseif ($msgType == "group") {
            $postArr = array(
                "message_type" => $msgType,
                "group_id" => $msgGroupId,
                //"user_id" => $msgSender,
                "message" => $postMsg
            );
        } elseif ($msgType == "private") {
            $postArr = array(
                "message_type" => $msgType,
                "user_id" => $msgSender,
                "message" => $postMsg
            );
        }
        $postData = json_encode($postArr);

        $botInfo = APP_BOT_INFO['QQGuild'][0];

        $postHeader[] = "Content-Type: application/json";
        $postHeader[] = "Authorization: " . $botInfo['accessToken'];

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            $postHeader
        );

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * QQ频道:返回请求API的结果
     *
     * 支持类型 at_msg、image_file、image_msg、json_msg、markdown_msg、reply_msg
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     *
     * @link https://q.qq.com
     */
    public function requestApiByQQGuild_2($newMsg, $msgExtArr = array())
    {
        /*
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        */
        $msgOrigMsg = $msgExtArr['msgOrigMsg'];
        $extMsgType = $msgExtArr['msgType'];

        $msgEventData = $msgOrigMsg['d'];

        $msgDirect = $msgEventData['direct_message'] ?? false;
        $msgChannelId = $msgEventData['channel_id'] ?? 0;
        $msgGuildId = $msgEventData['guild_id'] ?? 0;
        $msgSender = $msgEventData['author']['id'] ?? 0;
        $msgId = $msgEventData['id'] ?? NULL;

        if ($msgDirect) {
            /**
             *
             * 私信
             *
             */
            $reqUrl = APP_ORIGIN . "/dms/{$msgGuildId}/messages";
        } else {
            /**
             *
             * 频道
             *
             */
            $reqUrl = APP_ORIGIN . "/channels/{$msgChannelId}/messages";
        }

        $postArr['msg_id'] = $msgId;

        if ($extMsgType == "markdown_msg") {
            /**
             * 
             * markdown 模版
             * 
             */
            $postArr['markdown'] = json_decode($newMsg, true);
        } elseif ($extMsgType == "json_msg") {
            $postArr['ark'] = json_decode($newMsg, true);
        } elseif (strpos($extMsgType, "at_msg") > -1 || strpos($extMsgType, "reply_msg") > -1) {
            $postArr['content'] = "<@!{$msgSender}>{$newMsg}";
        } else {
            $postArr['content'] = $newMsg;
        }

        /**
         *
         * 移除这俩的 content ，用不到
         *
         */
        if (in_array($extMsgType, array("markdown_msg", "json_msg"))) {
            unset($postArr['content']);
        }

        if ($msgId) {
            if (strpos($extMsgType, "reply_msg") > -1) {
                $postArr['message_reference'] = array(
                    "message_id" => $msgId,
                    "ignore_get_message_error" => true
                );
            }
        }

        if (strpos($extMsgType, "image_msg") > -1) {
            $extMsgImgUrl = $msgExtArr['msgImgUrl'] ?? NULL;

            $postArr['image'] = $extMsgImgUrl;
        }

        if (strpos($extMsgType, "image_file") > -1) {
            $extMsgImgFile = $msgExtArr['msgImgFile'] ?? NULL;
            $extMsgImgFileType = getimagesize($extMsgImgFile);

            switch ($extMsgImgFileType['mime']) {
                case 'image/png':
                    $extMsgImgFileType = ".png";
                    break;

                case 'image/gif':
                    $extMsgImgFileType = ".gif";
                    break;

                case 'image/jpg':
                    $extMsgImgFileType = ".jpg";
                    break;

                case 'image/jpeg':
                    $extMsgImgFileType = ".jpeg";
                    break;

                case 'image/bmp':
                    $extMsgImgFileType = ".bmp";
                    break;

                case 'image/webp':
                    $extMsgImgFileType = ".webp";
                    break;
            }

            $postHeader[] = "Content-Type: multipart/form-data";

            $postArr['file_image'] = new CURLFile($extMsgImgFile, "multipart/form-data", TIME_T . $extMsgImgFileType);

            $postData = $postArr;
        } else {
            $postHeader[] = "Content-Type: application/json";

            $postData = json_encode($postArr);
        }

        $botInfo = APP_BOT_INFO['QQGuild'][1];

        $postHeader[] = "Authorization: Bot " . $botInfo['id'] . "." . $botInfo['accessToken'];

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            $postHeader
        );

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * QQ频道:返回请求API的结果
     *
     * 支持类型 at_msg、image_file、image_msg、json_msg、markdown_msg、reply_msg
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     *
     * @link https://q.qq.com
     */
    public function requestApiByQQGroup($newMsg, $msgExtArr = array())
    {
        /*
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        */
        $msgOrigMsg = $msgExtArr['msgOrigMsg'];
        $extMsgType = $msgExtArr['msgType'];

        $msgEventData = $msgOrigMsg['d'];

        $msgGroupId = $msgEventData['group_id'] ?? 0;
        $msgSender = $msgEventData['author']['id'] ?? 0;
        $msgId = $msgEventData['id'] ?? NULL;

        $extMsgType == "file_msg" ? $msgType = "files" : $msgType = "messages";

        if ($msgGroupId == 0) {
            /**
             *
             * 私信
             *
             */
            $reqUrl = APP_ORIGIN . "/v2/users/{$msgSender}/{$msgType}";
        } else {
            /**
             *
             * 群聊
             *
             */
            $reqUrl = APP_ORIGIN . "/v2/groups/{$msgGroupId}/{$msgType}";
        }

        $postArr['timestamp'] = TIME_T;
        $postArr['msg_id'] = $msgId;

        //$date_string_without_milliseconds = substr($msgTimestamp, 0, -3);
        //$date_object = new DateTime($date_string_without_milliseconds);
        //$timestamp = $date_object->getTimestamp();

        if ($extMsgType == "markdown_msg") {
            /**
             * 
             * 自定义 markdown
             * 
             */
            $postArr['content'] = "md";
            $postArr['msg_type'] = 2;
            $postArr['markdown'] = array(
                "content" => $newMsg
            );
        } elseif ($extMsgType == "file_msg") {
            unset($postArr['msg_id']);

            $postArr['content'] = "file";
            $postArr['event_id'] = $msgId;
            $postArr['file_type'] = 1;
            $postArr['srv_send_msg'] = true;
            $postArr['url'] = $newMsg;
        } elseif ($extMsgType == "json_msg") {
            $postArr['content'] = "json";
            $postArr['ark'] = json_decode($newMsg, true);
        } else {
            $postArr['content'] = $newMsg;
        }

        /**
         *
         * 移除这俩的 content ，用不到
         *
         */
        //if (in_array($extMsgType, array("markdown_msg", "json_msg"))) {
        //unset($postArr['content']);
        //}

        if ($msgId) {
            if (strpos($extMsgType, "reply_msg") > -1) {
                $postArr['message_reference'] = array(
                    "message_id" => $msgId,
                    "ignore_get_message_error" => true
                );
            }
        }

        if (strpos($extMsgType, "image_msg") > -1) {
            $extMsgImgUrl = $msgExtArr['msgImgUrl'] ?? NULL;

            $postArr['image'] = $extMsgImgUrl;
        }

        if (strpos($extMsgType, "image_file") > -1) {
            $extMsgImgFile = $msgExtArr['msgImgFile'] ?? NULL;
            $extMsgImgFileType = getimagesize($extMsgImgFile);

            switch ($extMsgImgFileType['mime']) {
                case 'image/png':
                    $extMsgImgFileType = ".png";
                    break;

                case 'image/gif':
                    $extMsgImgFileType = ".gif";
                    break;

                case 'image/jpg':
                    $extMsgImgFileType = ".jpg";
                    break;

                case 'image/jpeg':
                    $extMsgImgFileType = ".jpeg";
                    break;

                case 'image/bmp':
                    $extMsgImgFileType = ".bmp";
                    break;

                case 'image/webp':
                    $extMsgImgFileType = ".webp";
                    break;
            }

            $postHeader[] = "Content-Type: multipart/form-data";

            $postArr['file_image'] = new CURLFile($extMsgImgFile, "multipart/form-data", TIME_T . $extMsgImgFileType);

            $postData = $postArr;
        } else {
            $postHeader[] = "Content-Type: application/json";

            $postData = json_encode($postArr);
        }

        $botInfo = APP_BOT_INFO['QQGroup'];

        $postHeader[] = "Authorization: " . $GLOBALS['authorization'][FRAME_ID];
        $postHeader[] = "X-Union-Appid: " . $botInfo['id'];

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            $postHeader
        );

        if (APP_DEBUG)
            appDebug("output", $reqUrl . "\n\n" . $postData . "\n\n" . json_encode($postHeader) . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * X星球:返回请求API的结果
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     */
    public function requestApiByXXQ($newMsg, $msgExtArr = array())
    {
        $reqUrl = APP_ORIGIN . "/hero/v1/robot.php?type=sendMsgByXXQ";

        /*
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        */
        $msgOrigMsg = $msgExtArr['msgOrigMsg'];
        $extMsgType = $msgExtArr['msgType'];

        $msgGuildId = $msgOrigMsg['superGroupId'] ?? 0;
        $msgChannelId = $msgOrigMsg['chatRoomId'] ?? 0;
        $fUserId = $msgOrigMsg['fUserId'] ?? 0;
        $nickname = $msgOrigMsg['nickname'] ?? NULL;

        if ($extMsgType == "at_msg") {
            $msgAt = '[[\"24\",\"0\",\"2\",\"{\\\\\\"name\\\\\\":\\\\\\"' . $nickname . '\\\\\\",\\\\\\"atUserId\\\\\\":\\\\\\"' . $fUserId . '\\\\\\"}\"]]';
        } else {
            $msgAt = NUll;
        }

        $postData = "superGroupId={$msgGuildId}&chatRoomId={$msgChannelId}&msgAt={$msgAt}&message=" . urlencode($newMsg);

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData
        );

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     * 
     * 米游社:返回请求API的结果
     * 
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     */
    public function requestApiByMYS($newMsg, $msgExtArr = array())
    {
        /*
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        */
        $msgOrigMsg = $msgExtArr['msgOrigMsg'];
        $extMsgType = $msgExtArr['msgType'];

        $msgEventData = $msgOrigMsg['event'];
        $msgEventExtData = $msgEventData['extend_data']['EventData']['SendMessage'];

        $msgRobot = $msgEventData['robot']['template']['id'] ?? 0;
        $msgType = $msgEventData['type'] ?? 0;
        $msgSource = $msgEventExtData['villa_id'] ?? 0;
        $msgSubSource = $msgEventExtData['room_id'] ?? 0;
        $msgSender = $msgEventExtData['from_user_id'] ?? 0;
        $msgUser = json_decode($msgEventExtData['content'], true)['user'] ?? array();

        $reqUrl = APP_ORIGIN . "/vila/api/bot/platform/sendMessage";

        $entitiesList = array();

        /**
         * 
         * 渲染 at
         * 
         */
        if (strpos($extMsgType, "at_msg") > -1) {
            $msgUserName = $msgUser['name'];
            $msgUserNameLen = mb_strlen($msgUserName);

            $newMsg = "@{$msgUserName} {$newMsg}";

            $entitiesList[] = array(
                "offset" => 0,
                "length" => $msgUserNameLen + 2,
                "entity" => array(
                    "type" => "mentioned_user",
                    "user_id" => $msgUser['id']
                )
            );

            $isAtMsg = true;
        } else {
            $isAtMsg = false;
        }

        /**
         * 
         * 渲染 链接
         * 
         */
        $resArr = explode("\n", $newMsg);

        $resNum = 0;
        $resLineEmojiNum = 0;
        $resAllEmojiNum = 0;
        for ($x_i = 0; $x_i < count($resArr); $x_i++) {
            $forList = $resArr[$x_i];

            if (
                preg_match_all(
                    '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F900}-\x{1F9FF}\x{1F1E0}-\x{1F1FF}]/u',
                    $forList,
                    $matches_emoji
                )
            ) {
                $resLineEmojiNum++;
                //多少行有表情
            }

            $resAllEmojiNum = $resAllEmojiNum + count($matches_emoji[0]);
            //一行有多少表情
        }

        if ($resLineEmojiNum > 0) {
            $resNum = $resLineEmojiNum + 1;
        }

        if ($resAllEmojiNum >= 10) {
            $resNum = $resNum + (int) ($resAllEmojiNum / 10);
        }

        //$resNum = $resAllEmojiNum - 4;

        preg_match_all('/((https|http)?:\/\/)[^\s]+/u', $newMsg, $matches_url);

        foreach ($matches_url[0] as $match) {
            $link = $match;
            $offset = mb_strpos($newMsg, $link);
            $length = mb_strlen($link);

            $entitiesList[] = array(
                //"test" => mb_strlen($newMsg),
                "offset" => $offset + $resNum,
                "length" => $length,
                "entity" => array(
                    "type" => "link",
                    "url" => $link,
                    "requires_bot_access_token" => false
                )
            );
        }

        $extMsgImgUrl = $msgExtArr['msgImgUrl'] ?? NULL;

        if ($extMsgType == "image_msg") {
            $msgContent['content']['url'] = $extMsgImgUrl;

            $objectName = "MHY:Image";
        } else {
            $msgContent['content']['text'] = $newMsg;
            $msgContent['content']['entities'] = $entitiesList;

            if ($extMsgImgUrl) {
                $msgContent['content']['images'][] = array(
                    "url" => $extMsgImgUrl
                );
            }

            $objectName = "MHY:Text";
        }

        $postArr = array(
            "villa_id" => $msgSource,
            "room_id" => $msgSubSource,
            "object_name" => $objectName,
            "msg_content" => json_encode($msgContent)
        );

        $botInfo = APP_BOT_INFO['MYS'];
        $postData = json_encode($postArr);

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            array(
                "x-rpc-bot_id: " . $botInfo['id'],
                "x-rpc-bot_secret: " . $botInfo['accessToken'],
                "x-rpc-bot_villa_id: {$msgSource}",
                "Content-Type: application/json"
            )
        );

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * 处理被添加好友/进群请求
     *
     * @param string $code 0:忽略 10:同意 20:拒绝 30:单项同意
     * @param string $msg 理由
     */
    public function appHandleByMPQ($code, $msg = "")
    {
        $ret = json_encode(
            array(
                "Ret" => (string) $code,
                "Msg" => !$msg ? "" : $msg
            ),
            JSON_UNESCAPED_UNICODE
        );

        echo $ret;
    }

    /**
     *
     * 设置:消息类型
     *
     * 支持类型 api_msg、at_msg、image_file、image_msg、json_msg (ark_msg)、markdown_msg、reply_msg
     *
     * @param string $msgType 支持类型
     *
     */
    public function appSetMsgType($msgType = NULL)
    {
        $msgGc = $GLOBALS['msgGc'];

        if ($msgGc) {
            $GLOBALS['msgExt'][$msgGc]['msgType'] = $msgType;
        }
    }

    /**
     *
     * 发送:信息
     *
     */
    public function appSend($msgRobot, $msgType, $msgSource, $msgSender, $msgContent, $msgExtArr = array())
    {
        //if (!$msgContent) return;

        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode($msgExtData, true) : $newData = $msgExtData;
        }
        $msgOrigMsg = $newData['msgOrigMsg'];
        $extMsgType = $newData['msgType'];

        if (strpos($extMsgType, "at_msg") > -1) {
            $msgContent = "\n{$msgContent}";
        }

        if (APP_BOT_SHORT_URL != "91m.top") {
            $msgContent = str_replace("https://91m.top/s", "https://" . APP_BOT_SHORT_URL . "/s", $msgContent);
        }

        $msgContent = str_replace("[CONFIG_ADMIN]", implode(",", CONFIG_ADMIN), $msgContent);
        $msgContent = str_replace("[CONFIG_ROBOT]", implode(",", CONFIG_ROBOT), $msgContent);
        $msgContent = str_replace("[CONFIG_VERSION]", CONFIG_VERSION, $msgContent);
        $msgContent = str_replace("[TIME_T]", date("Y-m-d H:i:s a", TIME_T), $msgContent);
        $msgContent = str_replace("[PUSH_MSG_IMG]", "\n\n", $msgContent);
        //只回复图片的占位符

        //$msgContent = str_replace("\r", "\\r", $msgContent);
        //$msgContent = str_replace("\n", "\\n", $msgContent);
        //$msgContent = str_replace("\u", "\\u", $msgContent);

        $msgContent = rtrim($msgContent, "\n");

        if (FRAME_ID == 2500) {
            $ret = $this->requestApiByXIAOAI($msgContent, $newData);
        } elseif (FRAME_ID == 10000) {
            if ($extMsgType == "api_msg") {
                $newMsg = $msgContent;
            } elseif ($extMsgType == "json_msg") {
                $newMsg = "Api_SendAppMsg('{$msgRobot}',{$msgType},'{$msgSource}','{$msgSender}','{$msgContent}')";
            } elseif ($extMsgType == "xml_msg") {
                $newMsg = "Api_SendXml('{$msgRobot}',{$msgType},'{$msgSource}','{$msgSender}','{$msgContent}',0)";
            } else {
                if (strpos($extMsgType, "at_msg") > -1) {
                    $msgContent = "[@[QQ]]{$msgContent}";
                }

                $newMsg = "Api_SendMsg('{$msgRobot}',{$msgType},0,'{$msgSource}','{$msgSender}','{$msgContent}')";
            }

            $ret = $this->requestApiByMPQ($newMsg);
        } elseif (FRAME_ID == 15000) {
            $ret = $this->requestApiByOPQ($msgContent, $newData);
        } elseif (FRAME_ID == 20000) {
            $wechatTopic = APP_WECHAT_TOPIC;
            if ($wechatTopic) {
                $ret = $msgContent;
                $ret .= "\n----\n";
                $ret .= $wechatTopic;
            } else {
                $ret = $msgContent;
            }

            $ret = $this->requestApiByWSLY($ret, $newData);
        } elseif (FRAME_ID == 50000) {
            $ret = $this->requestApiByNOKNOK($msgContent, $newData);
        } elseif (FRAME_ID == 60000) {
            $ret = $this->requestApiByQQGuild_1($msgContent, $newData);
        } elseif (FRAME_ID == 70000) {
            $ret = $this->requestApiByQQGuild_2($msgContent, $newData);
            $resJson = json_decode($ret);
            $resCode = $resJson->code ?? 0;

            if ($resCode > 0) {
                /**
                 *
                 * 把报错都打印出来，方便处理
                 *
                 */
                appDebug("output", json_encode($newData) . "\n\n" . $ret);

                if ($resCode != 304023) {
                    $this->appSetMsgType("at_msg");

                    $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgUrl'] = NULL;
                    $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgFile'] = NULL;

                    $resMessage = $resJson->message;

                    $ret = "\n请求错误，请复制此消息反馈给开发者\n";
                    $ret .= "~~~~~\n";
                    $ret .= "错误代码:{$resCode}\n";
                    $ret .= "错误信息:{$resMessage}";

                    sleep(1);

                    $this->requestApiByQQGuild_2($ret, $newData);

                    return;
                }
            }
        } elseif (FRAME_ID == 75000) {
            $ret = $this->requestApiByQQGroup($msgContent, $newData);
        } elseif (FRAME_ID == 80000) {
            $ret = $this->requestApiByXXQ($msgContent, $newData);
        } elseif (FRAME_ID == 90000) {
            $ret = $this->requestApiByMYS($msgContent, $newData);
        } else {
            return;
        }

        return $ret;
    }

    ##### 以上为框架的出口，可以自行拓展

    /**
     *
     * 初始化 chatGPT
     *
     */
    public function appChatGPTInit($proxy = "")
    {
        $appInfo = APP_INFO;
        $apiKey = $appInfo['authInfo'][1005];

        $this->chatGPT = new ChatGpt("Bearer " . $apiKey[1], $apiKey[2]);

        if ($proxy) {
            Onekb\ChatGpt\Di::set("proxy", $proxy);
        }
    }

    /**
     *
     * 初始化拼音
     *
     */
    public function appPinyinInit()
    {
        $this->pinyin = new Pinyin();
    }

    /**
     *
     * 命令行错误示例
     *
     * @param string $keywords 关键词
     * @return string 返回错误时的示例
     */
    public function appCommandErrorMsg($keywords)
    {
        $this->appSetMsgType("at_msg");

        $keywordsInfo = $this->redisHget("robot:plugins:keywordsInfo", $keywords) ?? "未知错误";

        return "参数有误，" . $keywordsInfo;
    }

    /**
     *
     * 缩短链接
     *
     * @param string $longUrl 只有白名单內的链接才能被缩短
     * @return string 返回缩短后的链接
     */
    public function appGetShortUrl($longUrl)
    {
        $reqRet = $this->requestUrl(
            APP_API_APP . "?type=getShortUrl",
            "url=" . urlencode($longUrl),
            array(
                "Referer: https://bot.91m.top",
                DEFAULT_UA
            )
        );
        $resJson = json_decode($reqRet);
        $resData = $resJson->data;
        $ret = $resData->url ?? "缩短失败";

        return $ret;
    }

    /**
     *
     * 随机字符串
     *
     * @param string $len 长度
     * @param string $chars 填充字符串
     * @return string 返回生成的随机字符串
     */
    public function appGetRandomString($len = 6, $chars = NULL)
    {
        if (is_null($chars)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000 * (float) microtime());
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    /**
     *
     * 获取缓存图片
     *
     * @param string $imgName 图片名字
     * @return string 返回完整的缓存链接
     */
    public function appGetImgCache($path)
    {
        strpos(APP_ORIGIN, ":") > -1 ? $http = "http" : $http = "https";

        $arr = explode("/", $path);
        $arrNum = count($arr);

        $ret = $http . "://" . $_SERVER['SERVER_NAME'] . "/app/" . $arr[$arrNum - 3] . "/" . $arr[$arrNum - 2] . "/" . $arr[$arrNum - 1] . "?t=" . TIME_T;

        return $ret;
    }

    /**
     *
     * 图片、文本检测，输出的时候可能会包括用户输入内容的时候建议加入检测
     *
     * @param string $data 需要检测的内容
     * @param string $checkType MsgSecCheck 或 MediaCheckAsync
     * @param int $dataType $checkTyp 为 MediaCheckAsync 时，1:音频 2:图片
     * @return bool true:可能存在违规内容 false:正常
     *
     * @link https://q.qq.com/wiki/develop/miniprogram/server/open_port/port_safe.html
     */
    public function appMsgCheckData($data, $checkType = "MsgSecCheck", $dataType = 2)
    {
        $reqRet = $this->requestUrl(
            APP_API_ROBOT . "?type=system&aid=0&bid={$checkType}&cid=" . $dataType,
            "msg=" . urlencode($data),
            array(
                "Referer: https://bot.91m.top",
                DEFAULT_UA
            )
        );
        $resJson = json_decode($reqRet);
        $resStatus = $resJson->status;
        $resCode = $resStatus->code;

        return $resCode == 0 || $resCode == 200 ? false : true;
    }

    /**
     *
     * desription 判断是否gif动画
     *
     * @param string $imgPath 图片路径
     * @return bool true:是 false:否
     */
    public function appMsgCheckGif($imgPath)
    {
        $fp = fopen($imgPath, 'rb');
        $image_head = fread($fp, 1024);
        fclose($fp);

        return preg_match("/" . chr(0x21) . chr(0xff) . chr(0x0b) . 'NETSCAPE2.0' . "/", $image_head) ? false : true;
    }

    /**
     *
     * desription 压缩图片
     *
     * @param string $imgPath 图片路径
     * @param string $imgDist 压缩后保存路径
     *
     * @link http://www.yuqingqi.com/phpjiaocheng/994.html
     */
    public function appMsgImgNewSize($imgPath, $imgDist)
    {
        list($width, $height, $type) = getimagesize($imgPath);
        $newWidth = $width;
        $newHeight = $height;

        switch ($type) {
            case 1:
                $giftype = $this->appMsgCheckGif($imgPath);

                if ($giftype) {
                    header('Content-Type:image/gif');
                    $image_wp = imagecreatetruecolor($newWidth, $newHeight);
                    $image = imagecreatefromgif($imgPath);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagejpeg($image_wp, $imgDist, 75);
                    imagedestroy($image_wp);
                }

                break;

            case 2:
                header('Content-Type:image/jpeg');
                $image_wp = imagecreatetruecolor($newWidth, $newHeight);
                $image = imagecreatefromjpeg($imgPath);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($image_wp, $imgDist, 75);
                imagedestroy($image_wp);

                break;

            case 3:
                header('Content-Type:image/png');
                $image_wp = imagecreatetruecolor($newWidth, $newHeight);
                $image = imagecreatefrompng($imgPath);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($image_wp, $imgDist, 75);
                imagedestroy($image_wp);

                break;
        }
    }

    /**
     *
     * 将图片压缩缓存到服务器本地 app/cache/:inputPath
     *
     * @param string keywords 文件夹
     * @param string inputPath 输入路径文件夹名
     * @param string outputPath 输出路径文件夹名，和输入不同时将会替换
     * @param string imgUrl 需要下载的图片链接
     * @param string imgData base64的图片数据
     * @return array 返回图片名字和链接
     */
    public function appDownloadImg($msgSender, $keywords, $inputPath, $outputPath, $imgUrl = NULL, $imgData = NULL)
    {
        $dir = APP_DIR_CACHE;
        $imgDir = $dir . $inputPath;

        $imgName = md5($msgSender . $keywords . TIME_T) . "_temp.jpg";
        $imgPath = $imgDir . "/" . $imgName;

        $newImgName = str_replace("_temp", "", $imgName);
        $newImgPath = str_replace("_temp", "", $imgPath);

        /**
         *
         * 不存在自动创建文件夹
         *
         */
        if (!file_exists($imgDir)) {
            mkdir($imgDir, 0777, true);

            if ($inputPath != $outputPath) {
                mkdir($dir . $outputPath, 0777, true);
            }
        }

        file_put_contents($imgPath, $imgData ? $imgData : $this->requestUrl($imgUrl));

        /**
         *
         * 不压缩的只需重命名统一格式即可
         *
         */
        if ($GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgNewSize'] == false) {
            rename($imgPath, $newImgPath);
        } else {
            $this->appMsgImgNewSize($imgPath, $newImgPath);
            //压缩图片

            unlink($imgPath);
            //删除原件
        }

        //$newImgPath = str_replace($inputPath . "/", $outputPath . "/", $newImgPath);
        //输入替换成输出

        return array(
            "name" => $newImgName,
            "url" => $this->appGetImgCache($newImgPath)
        );
    }

    /**
     *
     * 十六进制:编码
     *
     */
    public function appStrToHex($str)
    {
        $hex = "";
        for ($i = 0; $i < strlen($str); $i++)
            $hex .= dechex(ord($str[$i]));
        $hex = strtoupper($hex);

        return $hex;
    }

    /**
     *
     * 十六进制:解码
     *
     */
    public function appHexToStr($hex)
    {
        $str = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2)
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));

        return $str;
    }

    /**
     *
     * 取中间
     *
     */
    public function appGetSubstr($str, $leftStr, $rightStr)
    {
        $left = strpos($str, $leftStr);
        //echo '左边:'.$left;
        $right = strpos($str, $rightStr, $left);
        //echo '<br>右边:'.$right;
        if ($left < 0 or $right < $left)
            return '';

        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }

    /**
     *
     * 计算 Gtk
     *
     */
    public function appGetGtk($str)
    {
        //$str = $cookie['skey'];
        $hash = 5381;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $h = ($hash << 5) + $this->utf8Unicode($str[$i]);
            $hash += $h;
        }

        return $hash & 0x7fffffff;
    }

    /**
     *
     * 计算 Gtk:utf8Unicode
     *
     */
    public function utf8Unicode($c)
    {
        switch (strlen($c)) {
            case 1:
                return ord($c);
            case 2:
                $n = (ord($c[0]) & 0x3f) << 6;
                $n += ord($c[1]) & 0x3f;
                return $n;
            case 3:
                $n = (ord($c[0]) & 0x1f) << 12;
                $n += (ord($c[1]) & 0x3f) << 6;
                $n += ord($c[2]) & 0x3f;
                return $n;
            case 4:
                $n = (ord($c[0]) & 0x0f) << 18;
                $n += (ord($c[1]) & 0x3f) << 12;
                $n += (ord($c[2]) & 0x3f) << 6;
                $n += ord($c[3]) & 0x3f;
                return $n;
        }
    }

    /**
     *
     * redis 删除
     *
     */
    public function redisDel($redisKey, $isMd5 = false)
    {
        return $this->redis->DEL($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis 是否存在
     *
     */
    public function redisExists($redisKey, $isMd5 = false)
    {
        return $this->redis->EXISTS($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis 获取
     *
     */
    public function redisGet($redisKey, $isMd5 = false)
    {
        $redisValue = $this->redis->GET($isMd5 ? md5($redisKey) : $redisKey);
        $resJson = json_decode($redisValue, true);

        return is_array($resJson) ? $resJson : $redisValue;
    }

    /**
     *
     * redis 匹配到的
     *
     */
    public function redisKeys($redisKey, $isMd5 = false)
    {
        return $this->redis->KEYS($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis 添加/修改
     *
     */
    public function redisSet($redisKey, $redisValue, $expireTime = NULL, $isMd5 = false)
    {
        $isMd5 ? $newRedisKey = md5($redisKey) : $newRedisKey = $redisKey;

        $this->redis->SET($newRedisKey, is_array($redisValue) ? json_encode($redisValue) : $redisValue);

        if ($expireTime) {
            $this->redis->EXPIRE($newRedisKey, $expireTime);
        }
    }

    /**
     *
     * redis 剩余时间
     *
     */
    public function redisTtl($redisKey, $isMd5 = false)
    {
        return $this->redis->TTL($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis db 选择
     *
     */
    public function redisSelect($dbIndex)
    {
        $this->redis->SELECT($dbIndex);
    }

    /**
     *
     * redis:Hdel 删除
     *
     */
    public function redisHdel($redisKey, $redisField, $isMd5 = false)
    {
        return $this->redis->HDEL($isMd5 ? md5($redisKey) : $redisKey, $isMd5 ? md5($redisField) : $redisField);
    }

    /**
     *
     * redis:Hexists 是否存在
     *
     */
    public function redisHexists($redisKey, $redisField, $isMd5 = false)
    {
        return $this->redis->HEXISTS($isMd5 ? md5($redisKey) : $redisKey, $isMd5 ? md5($redisField) : $redisField);
    }

    /**
     *
     * redis:Hget 获取
     *
     */
    public function redisHget($redisKey, $redisField, $isMd5 = false)
    {
        $redisValue = $this->redis->HGET($isMd5 ? md5($redisKey) : $redisKey, $isMd5 ? md5($redisField) : $redisField);
        $resJson = json_decode($redisValue, true);

        return is_array($resJson) ? $resJson : $redisValue;
    }

    /**
     *
     * redis:Hkeys 获取 key
     *
     */
    public function redisHkeys($redisKey, $isMd5 = false)
    {
        return $this->redis->HKEYS($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis:Hgetall 获取 key、value
     *
     */
    public function redisHgetall($redisKey, $isMd5 = false)
    {
        return $this->redis->HGETALL($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis:Hset 设置
     *
     */
    public function redisHset($redisKey, $redisField, $redisValue, $expireTime = NULL, $isMd5 = false)
    {
        $isMd5 ? $newRedisKey = md5($redisKey) : $newRedisKey = $redisKey;
        $isMd5 ? $newRedisField = md5($redisField) : $newRedisField = $redisField;

        $this->redis->HSET($newRedisKey, $newRedisField, is_array($redisValue) ? json_encode($redisValue) : $redisValue);

        if ($expireTime) {
            $this->redis->EXPIRE($newRedisKey, $expireTime);
        }
    }

    /**
     *
     * 网页访问，301、302 返回 User-Agent
     *
     */
    public function requestUrl($url, $postData = "", $headers = array(DEFAULT_UA), $cookies = "", $proxy = "")
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($postData && !strpos($postData, "getHeaders")) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        if ($cookies) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookies);
        }

        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

        $resData = curl_exec($ch);
        $resHeaders = curl_getinfo($ch);

        if (strpos($postData, "getHeaders") > -1 && $resHeaders['http_code'] != 200) {
            $resData = $resHeaders;
        }

        curl_close($ch);

        return $resData;
    }
}