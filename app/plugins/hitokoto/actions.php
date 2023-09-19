<?php

/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class hitokoto_actions extends app
{
    function __construct(&$appManager)
    {
        //注册这个插件
        //第一个参数是钩子的名称
        //第二个参数是 appManager 的引用
        //第三个是插件所执行的方法
        $appManager->register('plugin', $this, 'EventFun');
    }
    //解析函数的参数是 appManager 的引用

    function EventFun($msg)
    {
        $msgPort = $msg['Port'];
        //监听的服务端口，范围为 8010-8020
        $msgPid = $msg['Pid'];
        //进程ID
        $msgVer = $msg['Ver'];
        //机器人版本
        $msgId = $msg['MsgID'];
        ///信息序号
        $msgRobot = $msg['Robot'];
        //参_机器人
        $msgType = $msg['MsgType'];
        //参_信息类型
        $msgSubType = $msg['MsgSubType'];
        //参_信息子类型
        $msgSource = $msg['Source'];
        //参_信息来源
        $msgSender = $msg['Sender'];
        //参_触发对象_主动
        $msgReceiver = $msg['Receiver'];
        //参_触发对象_被动
        $msgContent = base64_decode($msg['Content']);
        //参_信息内容
        $msgOrigMsg = base64_decode($msg['OrigMsg']);
        //参_原始信息

        if (in_array($msgSource, APP_SPECIAL_GROUP)) return;
        //特殊群

        $this->appSetMsgType();
        $msgContent = str_replace(" ", "", $msgContent);

        if (preg_match("/^(一言|网易云热评|舔狗日记|土味情话|互删句子)$/", $msgContent, $msgMatch)) {
            //$matchValue = $msgMatch[0];
            //$msgContent = str_replace($matchValue, "", $msgContent);

            if (FRAME_ID == 50000) {
                $this->appSetMsgType("markdown_msg");
            } elseif (FRAME_ID == 70000) {
                $this->appSetMsgType("reply_msg");
            } else {
                $this->appSetMsgType("at_msg");
            }

            if ($msgContent == "一言") {
                $ret = $this->getHitokoto();
            } elseif ($msgContent == "网易云热评") {
                $ret = $this->getMusicHotComment();
            } elseif ($msgContent == "舔狗日记") {
                $ret = $this->getTianXingByTianGou();
            } elseif ($msgContent == "土味情话") {
                $ret = $this->getTianXingByTuWei();
            } elseif ($msgContent == "互删句子") {
                $ret = $this->getTianXingByHuShan();
            }
        } elseif (in_array($msgSender, CONFIG_ADMIN)) {
            if ($msgContent == "句子状态") {
                $this->appSetMsgType("at_msg");

                $ret = $this->getHitokotoStatus();
            }
        }

        if (isset($ret)) {
            $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
        }
    }

    /**
     *
     * 获取一言
     *
     */
    function getHitokoto()
    {
        $reqRet = $this->requestUrl("https://v1.hitokoto.cn");
        $resJson = json_decode($reqRet);
        $resId = $resJson->id;
        $resFrom = $resJson->from;
        $resHitokoto = $resJson->hitokoto;

        $ret = "{$resHitokoto} - {$resFrom}";

        return FRAME_ID == 50000 ? "[{$ret}](https://hitokoto.cn?id={$resId})" : $ret;
    }

    /**
     *
     * 获取一言状态
     *
     */
    function getHitokotoStatus()
    {
        $reqRet = $this->requestUrl("https://status.hitokoto.cn/v1/statistic");
        $resJson = json_decode($reqRet);
        $resData = $resJson->data;
        $resVersion = $resData->version;
        $resStatus = $resData->status;
        $resTotal = $resStatus->hitokoto->total;
        $resCategory = implode(",", $resStatus->hitokoto->category);

        $load = $resStatus->load;

        $load_1 = floor($load[0] * 100) / 100;
        $load_2 = floor($load[1] * 100) / 100;
        $load_3 = floor($load[2] * 100) / 100;

        $resMemory = floor($resStatus->memory * 100) / 100;

        $resRequests = $resData->requests->all;
        $pastDay = $resRequests->past_day;
        $pastHour = $resRequests->past_hour;
        $pastMinute = $resRequests->past_minute;

        $ret = "一言统计信息 ↓\n";
        $ret .= "当前版本:{$resVersion}\n";
        $ret .= "句子总数:{$resTotal}\n";
        $ret .= "现存分类:{$resCategory}\n";
        $ret .= "服务负载:{$load_1},{$load_2},{$load_3}\n";
        $ret .= "内存占用:{$resMemory} MB\n";
        $ret .= "每分请求:{$pastMinute}\n";
        $ret .= "每时请求:{$pastHour}\n";
        $ret .= "当日请求:{$pastDay}";

        return $ret;
    }

    /**
     *
     * 获取网易云热评
     *
     * @link https://docs.tenapi.cn/comment.html#%E8%AF%B7%E6%B1%82url
     */
    function getMusicHotComment()
    {
        $reqRet = $this->requestUrl("https://tenapi.cn/comment/");
        $resJson = json_decode($reqRet);
        $resData = $resJson->data;
        $resId = $resData->id;
        $resSong = $resData->song;
        $resContent = $resData->content;
        $resContent = str_replace("n", " ", $resContent);

        $ret = "{$resContent} - {$resSong}";

        return FRAME_ID == 50000 ? "[{$ret}](https://music.163.com/#/song?id={$resId})" : $ret;
    }

    /**
     *
     * 获取舔狗日记
     *
     * @link https://www.tianapi.com/apiview/180
     */
    function getTianXingByTianGou()
    {
        $appInfo = APP_INFO;
        $key = $appInfo['authInfo'][1003][0];

        $reqRet = $this->requestUrl(
            "http://api.tianapi.com/tiangou/index",
            "key=" . $key
        );
        $resJson = json_decode($reqRet);
        $resData = $resJson->newslist[0]->content ?? NULL;

        if (!$resData) {
            $ret = $appInfo['codeInfo'][1002];
        } else {
            $ret = $resData;
        }

        return $ret;
    }

    /**
     *
     * 获取土味情话
     *
     * @link https://www.tianapi.com/apiview/80
     */
    function getTianXingByTuWei()
    {
        $appInfo = APP_INFO;
        $key = $appInfo['authInfo'][1003][0];

        $reqRet = $this->requestUrl(
            "http://api.tianapi.com/saylove/index",
            "key=" . $key
        );
        $resJson = json_decode($reqRet);
        $resData = $resJson->newslist[0]->content ?? NULL;

        if (!$resData) {
            $ret = $appInfo['codeInfo'][1002];
        } else {
            $ret = $resData;
        }

        return $ret;
    }

    /**
     *
     * 获取互删句子
     *
     * @link https://www.tianapi.com/apiview/193
     */
    function getTianXingByHuShan()
    {
        $appInfo = APP_INFO;
        $key = $appInfo['authInfo'][1003][0];

        $reqRet = $this->requestUrl(
            "http://api.tianapi.com/hsjz/index",
            "key=" . $key
        );
        $resJson = json_decode($reqRet);
        $resData = $resJson->newslist[0]->content ?? NULL;

        if (!$resData) {
            $ret = $appInfo['codeInfo'][1002];
        } else {
            $ret = $resData;
        }

        return $ret;
    }
}
