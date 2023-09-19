<?php

/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class demo_actions extends app
{
    function __construct(&$appManager)
    {
        //注册这个插件
        //第一个参数是钩子的名称
        //第二个参数是 appManager 的引用
        //第三个是插件所执行的方法
        $appManager->register('plugin', $this, 'EventFun');

        /**
         *
         * 下面有使用 appCommandErrorMsg 的，就必须要连 redis
         *
         */
        $this->linkRedis();
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

        if (in_array($msgSource, APP_SPECIAL_GROUP))
            return;
        //特殊群

        /**
         *
         * 这是返回时的消息类型，除了 MyPCQQ 机器，其他的都得特殊处理
         *
         * @link https://jmglsi.coding.net/public/bot.91m.top/backend/git/files#user-content-%E6%95%B0%E6%8D%AE
         */
        //$this->appSetMsgType();
        $msgContent = str_replace(" ", "", $msgContent);

        /**
         *
         * 正则匹配
         *
         */
        if (preg_match("/^(你好|我好|大家好)$/", $msgContent, $msgMatch)) {
            FRAME_ID == 70000 ? $this->appSetMsgType("reply_msg") : $this->appSetMsgType("at_msg");

            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);
            //将关键词替换掉

            /**
             *
             * 返回一句一言
             *
             */
            $ret = $this->getHitokoto();
        } elseif ($msgContent == "测试") {
            //$imgUrl = "https://www.baidu.com/img/flexible/logo/pc/result.png";
            $imgUrl = "https://upload-bbs.miyoushe.com/upload/2023/07/01/79316441/e886cbb5d98ce25cb1ffd6d3020b459f_102478842425828713.jpg";

            if ($imgUrl) {
                /**
                 *
                 * 返回纯图片的占位符
                 *
                 */
                $ret = "这是一段话";

                if (FRAME_ID == 10000) {
                    $this->appSetMsgType("at_msg");

                    $ret .= "[{$imgUrl}]";
                } elseif (FRAME_ID == 20000) {
                    $ret .= "无图片查看可以网页查看哦~\n";
                    $ret .= $this->appGetShortUrl($imgUrl);
                } elseif (FRAME_ID == 50000) {
                    $this->appSetMsgType("markdown_msg");

                    $ret .= "![]({$imgUrl})";
                } else {
                    $this->appSetMsgType("at_msg,image_msg");

                    $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgUrl'] = $imgUrl;
                }
            } else {
                $ret = APP_INFO['codeInfo'][1002];
            }
        } elseif (FRAME_ID == 70000 && $msgContent == "json") {
            $this->appSetMsgType("json_msg");

            $ret = '{"template_id":1,"kv":[{"key":"#DESC#","value":"机器人订阅消息"},{"key":"#PROMPT#","value":"XX机器人"},{"key":"#TITLE#","value":"XX机器人消息"},{"key":"#META_LIST#","obj":[{"obj_kv":[{"key":"name","value":"aaa"},{"key":"age","value":"3"}]},{"obj_kv":[{"key":"name","value":"bbb"},{"key":"age","value":"4"}]}]}]}';
        } elseif (FRAME_ID == 70000 && $msgContent == "md") {
            $this->appSetMsgType("markdown_msg");

            $ret = '{"custom_template_id":1,"params":[{"key":"title","values":["标题"]},{"key":"para1","values":["段落1"]},{"key":"para2","values":["段落2"]},{"key":"desc","values":["简介"]},{"key":"content","values":["在这个子频道非常开心"]},{"key":"link_introduction","values":["链接介绍"]}]}';
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
}