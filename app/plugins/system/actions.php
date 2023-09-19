<?php

/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class system_actions extends app
{
    function __construct(&$appManager)
    {
        //注册这个插件
        //第一个参数是钩子的名称
        //第二个参数是 appManager 的引用
        //第三个是插件所执行的方法
        $appManager->register('plugin', $this, 'EventFun');

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

        $this->appSetMsgType();
        $msgContent = str_replace(" ", "", $msgContent);

        if (preg_match("/加群/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);

            if (FRAME_ID == 10000 && $msgType == 1 && in_array($msgSender, CONFIG_ADMIN)) {
                if (!$msgContent)
                    return;

                $this->appInviteInGroup($msgRobot, $msgContent, $msgSender);
            } elseif (FRAME_ID == 20000 && $msgType == 100) {
                $inviteInGroup = APP_INFO['inviteInGroup'];

                $this->appInviteInGroup($msgRobot, $inviteInGroup[array_rand($inviteInGroup)], $msgSender);
            } else {
                $ret = $this->appCommandErrorMsg($matchValue);
            }
        } elseif (preg_match("/订阅/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);

            $appInfo = APP_INFO;

            if (!$msgContent || $msgContent == "列表") {
                $ret = $this->getRssInfo($msgContent);
            } else {
                if (FRAME_ID == 70000) {
                    $nowMsg = json_decode($msgOrigMsg);
                    $nowData = $nowMsg->d;
                    $roles = $nowData->member->roles;
                } else {
                    $roles = array();
                }

                /**
                 *
                 * QQ频道 2:管理员 4:频道主 5:子频道管理员
                 *
                 */
                if (in_array($msgSender, CONFIG_ADMIN) || in_array(2, $roles) || in_array(4, $roles) || in_array(5, $roles)) {
                    $ret = $this->updateRssInfo($msgContent);
                } else {
                    $ret = $appInfo['codeInfo'][1000];
                }
            }
        } elseif (preg_match("/^(功能|菜单|帮助)$/", $msgContent, $msgMatch)) {
            $ret = "> 游戏相关\n";

            if (in_array(FRAME_ID, array(10000))) {
                $ret .= "> 内战系统\n";
            }

            $ret .= "> 娱乐功能\n";
            $ret .= "> 系统功能\n";
            $ret .= "~~~~~\n";
            $ret .= "输入【上方分组】，例如【游戏相关】查看技能列表\n";
            $ret .= "> 介绍 https://91m.top/s/yuque";
        } elseif (preg_match("/^(喜欢黑色吗|原神|王者荣耀|游戏相关|内战系统|娱乐功能|系统功能)$/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];

            $menuArr['喜欢黑色吗'] = "游戏";
            $menuArr['原神'] = "原神";
            $menuArr['王者荣耀'] = "王者荣耀";
            $menuArr['游戏相关'] = "游戏";
            $menuArr['内战系统'] = "内战";
            $menuArr['娱乐功能'] = "工具";
            $menuArr['系统功能'] = "系统";

            $ret = $this->getPluginsInfo($menuArr[$matchValue]);
        } elseif ($msgContent == "群组") {
            if (in_array(FRAME_ID, array(10000, 20000))) {
                $ret = "机器人:{$msgRobot} 当前群号:{$msgSource} 您的账号:" . $msgSender;
            } elseif (FRAME_ID == 50000) {
                $nowMsg = json_decode($msgOrigMsg);
                $nowData = $nowMsg->data[0];

                $ret = "机器人:{$msgRobot} 当前频道:{$msgSource} 子频道:" . $nowData->target_id . " 您的账号:{$msgSender} ts:" . $nowData->ts . " nonce:" . $nowData->nonce;
            } elseif (FRAME_ID == 60000) {
                $nowMsg = json_decode($msgOrigMsg);

                $ret = "机器人:{$msgRobot} 当前频道:{$msgSource} 子频道:" . $nowMsg->channel_id . " 您的账号:" . $msgSender;
            } elseif (FRAME_ID == 70000) {
                $nowMsg = json_decode($msgOrigMsg);

                $ret = "机器人:{$msgRobot} 当前频道:{$msgSource} 子频道:" . $nowMsg->d->channel_id . " 您的账号:" . $msgSender;
            } elseif (FRAME_ID == 80000) {
                $nowMsg = json_decode($msgOrigMsg);

                $ret = "机器人:{$msgRobot} 当前频道:{$msgSource} 子频道:" . $nowMsg->chatRoomId . " 您的账号:" . $msgSender;
            } else {
                return;
            }

            //获取群组信息
        } elseif ($msgContent == "系统状态") {
            $ret = $this->getSystemInfo();

            //获取系统状态
        } elseif ($msgContent == "频道数据") {
            $ret = "主人，当前一共加了【21】个，其中活跃频道【15】个~";
        } elseif (in_array($msgSender, CONFIG_ADMIN)) {
            if (preg_match("/复述/", $msgContent, $msgMatch)) {
                $matchValue = $msgMatch[0];
                $msgContent = str_replace($matchValue, "", $msgContent);

                if ($msgContent) {
                    if (strpos($msgContent, '{"') > -1) {
                        $nowMsgType = "json_msg";
                    } elseif (strpos($msgContent, 'xml') > -1) {
                        $nowMsgType = "xml_msg";
                    } else {
                        $nowMsgType = NULL;
                    }

                    $this->appSetMsgType($nowMsgType);

                    $ret = $msgContent;
                } else {
                    $ret = $this->appCommandErrorMsg($matchValue);
                }
            } elseif (preg_match("/拉黑|加黑/", $msgContent, $msgMatch)) {
                $matchValue = $msgMatch[0];
                $msgContent = str_replace($matchValue, "", $msgContent);

                if ($msgContent) {
                    $ret = $this->addBlockList($msgContent);
                } else {
                    $ret = $this->appCommandErrorMsg($matchValue);
                }

                //添加黑名单
            } elseif (preg_match("/删黑/", $msgContent, $msgMatch)) {
                $matchValue = $msgMatch[0];
                $msgContent = str_replace($matchValue, "", $msgContent);

                if ($msgContent) {
                    $ret = $this->deleteBlockList($msgContent);
                } else {
                    $ret = $this->appCommandErrorMsg($matchValue);
                }

                //删除黑名单
            } elseif ($msgContent == "黑名单") {
                $ret = $this->getBlockList();

                //获取黑名单
            } elseif ($msgContent == "清除系统缓存") {
                $appDirCache = APP_DIR_CACHE;

                $ret = $this->cleanAppCache(substr($appDirCache, 0, strlen($appDirCache) - 1));

                //清除系统缓存
            } elseif ($msgContent == "清除网站缓存") {
                $ret = $this->cleanWebCache();

                //清除系统缓存
            }
        } elseif (FRAME_ID == 10000 && $msgContent == "登录") {
            $ret = $this->getMpqLoginQrcode($msgRobot, $msgType, $msgSource, $msgSender);
        } elseif (FRAME_ID == 20000 && preg_match("/http/", $msgContent, $msgMatch)) {
            $msgFileUrl = $msg['MsgFileUrl'] ?? NULL;
            if ($msgType == 100 && $msgFileUrl) {
                $ret = $this->appGetShortUrl($msgContent);
            }
        }

        if (isset($ret)) {
            $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
        }

        //$this->appGcInterconnected($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }

    /**
     *
     * 获取订阅列表
     *
     */
    function getRssInfo($msgContent)
    {
        $reqRet = $this->requestUrl(
            APP_API_ROBOT . "?type=rss&frameId=" . FRAME_ID . "&aid=0",
            "robotUin=" . $GLOBALS['msgRobot'] . "&msgSource=" . $GLOBALS['msgGc'] . "&msgUin=" . $GLOBALS['msgSender'],
            array(
                "Referer: https://bot.91m.top",
                DEFAULT_UA
            )
        );
        $resJson = json_decode($reqRet);
        $resData = $resJson->data;
        $resStatus = $resJson->status;
        $resResult = $resData->result;
        $resRssInfo = $resData->rssInfo;
        $resArr = $resResult->rows ?? NULL;
        $resArrNum = count($resArr);

        if ($resStatus->code != 200) {
            $ret = $resStatus->msg;

            return $ret;
        }

        $rssKey = array_column($resRssInfo, "name");

        $ret = "";

        if (!$msgContent || $resArrNum == 0) {
            $ret .= implode(",", $rssKey) . "\n";
            $ret .= "~~~~~\n";
            $ret .= "快来订阅吧，订阅<项目名>，示例:订阅王者公告";

            return $ret;
        }

        for ($rss_i = 0; $rss_i < $resArrNum; $rss_i++) {
            $forList = $resArr[$rss_i];

            $rssId = $forList->rssId;
            $switch = $forList->switch;

            $switch == 1 ? $switchText = "已订阅" : $switchText = "x";

            $ret .= "{$rssId} {$switchText}\n";
        }
        $ret .= "~~~~~\n";
        $ret .= "更多订阅即将上线 ;D";

        return $ret;
    }

    /**
     *
     * 更新订阅
     *
     */
    function updateRssInfo($msgContent)
    {
        $reqRet = $this->requestUrl(
            APP_API_ROBOT . "?type=rss&frameId=" . FRAME_ID . "&aid=1",
            "robotUin=" . $GLOBALS['msgRobot'] . "&msgSource=" . $GLOBALS['msgGc'] . "&msgUin=" . $GLOBALS['msgSender'] . "&rssId=" . $msgContent,
            array(
                "Referer: https://bot.91m.top",
                DEFAULT_UA
            )
        );
        $resJson = json_decode($reqRet);
        $resData = $resJson->data;
        $resStatus = $resJson->status;

        if ($resStatus->code != 200) {
            $ret = $resStatus->msg;
        } else {
            $ret = $resData;
        }

        return $ret;
    }

    /**
     *
     * 清除系统缓存
     *
     * @link https://www.cnblogs.com/itbsl/p/10430718.html
     */
    function cleanAppCache($aDir)
    {
        if ($handle = @opendir($aDir)) {
            $dirList = array();
            while (($fDir = readdir($handle)) !== false) {
                if ($fDir == "." || $fDir == "..") {
                    continue;
                }

                $dirList[] = $fDir;

                $sDir = "{$aDir}/{$fDir}";

                if (is_dir($sDir)) {
                    $this->cleanAppCache($sDir);
                } else {
                    unlink($sDir);
                }
            }
            @closedir($handle);

            rmdir($aDir);
        }

        $ret = "清除系统缓存:\n";
        $ret .= implode(",", $dirList);

        return $ret;
    }

    /**
     *
     * 清除网站缓存
     *
     */
    function cleanWebCache()
    {
        $keyList = array("app:info:*");

        for ($k_i = 0; $k_i < count($keyList); $k_i++) {
            $forList = $keyList[$k_i];

            $this->cleanRedis($forList, 0);
            $this->cleanRedis($forList, 1);
        }

        $ret = "清除网站缓存:\n";
        $ret .= implode(",", $keyList);

        return $ret;
    }

    /**
     *
     * 清除 Redis 缓存
     *
     */
    function cleanRedis($key, $dbIndex = 0)
    {
        if ($dbIndex > 0) {
            //$this->redisSelect($dbIndex);
        }

        $resArr = $this->redisKeys($key);

        foreach ($resArr as $value) {
            $this->redisDel($value);
        }

        if ($dbIndex > 0) {
            //$this->redisSelect(1);
        }
    }

    /**
     *
     * appNode 控制面板自带开发文档，获取系统信息，配合 F12 使用
     *
     * @link https://www.kancloud.cn/appnode/apidoc/504312
     * @link http://apidoc.cn/explore
     */
    function getSystemInfo()
    {
        $key = APP_INFO['authInfo'][1002][0];
        $host = "http://127.0.0.1:8899";

        /**
         *
         * 获取系统信息
         *
         */
        $url_1 = "api_action=Status.Overview&api_agent_app=sysinfo&api_nodeid=1&api_nonce=" . $this->appGetRandomString(16) . "&api_timestamp=" . TIME_T;
        $sign_1 = hash_hmac("md5", $url_1, $key);

        $newUrl_1 = $host . "/?{$url_1}&api_sign=" . $sign_1;

        $reqRet_1 = $this->requestUrl($newUrl_1);
        $resJson_1 = json_decode($reqRet_1);
        $resData_1 = $resJson_1->DATA;
        $CPUUseRate = $resData_1->CPUUseRate;
        $UpTime = $resData_1->UpTime;
        $LoadAvg = $resData_1->LoadAvg;
        $MemInfo = $resData_1->MemInfo;
        $Disks = $resData_1->Disks[0];

        /**
         *
         * 获取网络信息
         *
         */
        $url_2 = "api_action=Network.Info&api_agent_app=sysinfo&api_nodeid=1&api_nonce=" . $this->appGetRandomString(16) . "&api_timestamp=" . TIME_T;
        $sign_2 = hash_hmac("md5", $url_2, $key);

        $newUrl_2 = $host . "/?{$url_2}&api_sign=" . $sign_2;

        $reqRet_2 = $this->requestUrl($newUrl_2);
        $reqRet_2 = str_replace("K/", " K/", $reqRet_2);
        $reqRet_2 = str_replace("B/", " B/", $reqRet_2);
        $reqRet_2 = str_replace("M/", " M/", $reqRet_2);
        $reqRet_2 = str_replace("G", " G", $reqRet_2);

        $resJson_2 = json_decode($reqRet_2);
        $resData_2 = $resJson_2->DATA;
        $NetworkCards = $resData_2->NetworkCards[0];

        $ret = "SDK/PHP:" . CONFIG_VERSION . " / " . PHP_VERSION . "\n";
        $ret .= "运行时间:" . (floor($UpTime->Total * 100 / 86400) / 100) . " 天\n";
        $ret .= "内存使用:" . str_replace("G", "", $MemInfo->MemUsed) . " / " . str_replace("G", "", $MemInfo->MemTotal) . " G\n";
        $ret .= "存储空间:" . str_replace("G", "", $Disks->Used) . " / " . str_replace("G", "", $Disks->Total) . " G\n";
        $ret .= "当前负载:" . ($CPUUseRate * 100) . " % CPU使用率:" . $LoadAvg->Last1MinRate . " / " . $LoadAvg->Last5MinRate . " / " . $LoadAvg->Last15MinRate . " %\n";
        $ret .= "网络接口:↑ " . $NetworkCards->TXSpeed . " | " . $NetworkCards->TX . " / ↓ " . $NetworkCards->RXSpeed . " | " . $NetworkCards->RX . "\n";
        $ret .= "操作系统:" . php_uname();

        return $ret;
    }

    /**
     *
     * 菜单
     *
     */
    function getPluginsInfo($menuType = NULL)
    {
        $plugins = $this->getPlugins();
        $pluginsNum = count($plugins);

        $commandIndex = 0;
        $allTimes = 0;
        $allCommand = "";
        $allTrigger = array();
        $allKeywords = "";
        $adminCommand = "";
        $commonCommand = "";
        foreach ($plugins as $plugin) {
            $pName = $plugin['name'];
            $pPath = $plugin['path'];

            $configPath = $pPath . "/config.json";
            $reqRet = file_get_contents($configPath);
            $resJson = json_decode($reqRet);

            $pluginSwitch = $resJson->switch;

            if (!$pluginSwitch)
                continue;

            $pluginType = $resJson->type;
            $pluginName = $resJson->name;
            $pluginDesc = $resJson->desc;
            $pluginFrame = $resJson->trigger->frame;
            //$resJson->switch ? $pluginSwitch = "✔︎" : $pluginSwitch = "✗";

            if ($pluginFrame != [] && !in_array(FRAME_ID, $pluginFrame))
                continue;

            $pluginCommand = $resJson->trigger->command;
            foreach ($pluginCommand as $commandList) {
                //$n = $commandIndex + 1;

                $keywords = $commandList->keywords;
                $keywordsArr = explode("|", $keywords);
                $desc = $commandList->desc;
                $demo = $commandList->demo ?? NULL;
                $show = $commandList->show ?? true;

                $keywordsArr_1 = $keywordsArr[0];

                if ($keywordsArr_1 == "(.*?)")
                    continue;
                //跳过全部触发

                $allKeywords .= $keywords . "|";

                for ($keywordsArr_i = 0; $keywordsArr_i < count($keywordsArr); $keywordsArr_i++) {
                    $forList = $keywordsArr[$keywordsArr_i];

                    $times = (int) $this->redisHget("robot:plugins:analysis", $forList);
                    $allTimes = $allTimes + $times;

                    if ($demo) {
                        $this->redisHset("robot:plugins:keywordsInfo", $forList, $demo);
                    }

                    $allTrigger[$forList] = $plugin;
                }

                if (!$show)
                    continue;

                $command = $keywordsArr_1 . " - {$desc}\n";

                if ($menuType && !in_array($menuType, $pluginType))
                    continue;
                //跳过不是一个类型的

                if (strpos($desc, "[管]") > -1) {
                    //$adminCommand .= $command;
                } else {
                    $commonCommand .= $command;
                }

                $commandIndex++;
            }
            //$allCommand .= $commonCommand . $adminCommand;
            //$ret .= "[{$pluginSwitch}]{$pluginName} {$pluginDesc}\n";
        }
        $allKeywords = substr($allKeywords, 0, strlen($allKeywords) - 1);

        $adminCommand = str_replace("[管]", "", $adminCommand);

        $triggerNum = count($allTrigger);
        $nowAllTimes = floor(($allTimes / 10000) * 100) / 100;

        //$allCommand .= "以下为所有人命令:\n";
        $allCommand .= $commonCommand;
        $allCommand .= "~~~~~\n";
        //$allCommand .= "以下为管理员命令:\n";
        //$allCommand .= $adminCommand;
        //$allCommand .= "~~~~~\n";
        $allCommand .= "发送【功能】返回【主菜单】\n";
        $allCommand .= "~~~~~\n";
        $allCommand .= "插件/钩子/调用:{$pluginsNum}/{$triggerNum}/{$nowAllTimes}w";

        $this->redisHset("robot:config", "allTrigger:" . FRAME_ID, $allTrigger);
        $this->redisHset("robot:config", "allKeywords:" . FRAME_ID, "/^(\#|\/|\!)?({$allKeywords})/i");

        return $allCommand;
    }

    /**
     *
     * 获取登录二维码
     *
     */
    function getMpqLoginQrcode($msgRobot, $msgType, $msgSource, $msgSender)
    {
        $newData = "Api_GetLoginQRCode()";

        $imgUrl = $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, NULL, array("msgType" => "api_msg", "msgOrigMsg" => $newData));

        $reqRet = $this->appDownloadImg($msgSender, "mpqLoginQrcode", "mpqLoginQrcode", "mpqLoginQrcode", NULL, base64_decode($imgUrl));

        $img = $reqRet['url'];

        $ret = "请打开链接，使用摄像头扫码，有效期很短\n";
        $ret .= $img;

        return $ret;
    }

    /**
     *
     * 添加黑名单
     *
     */
    function addBlockList($msgSender)
    {
        $sender = NULL;

        if (FRAME_ID == 10000) {
            $sender = $this->appGetSubstr($msgSender, "[@", "]");
        } elseif (FRAME_ID == 20000) {
            $sender = $this->appGetSubstr($msgSender, "wxid=", "]");
        } elseif (FRAME_ID == 60000) {
            $sender = $this->appGetSubstr($msgSender, "qq=", "]");
        } elseif (FRAME_ID == 70000) {
            $sender = $this->appGetSubstr($msgSender, "<@!", ">");
        } else {
            return;
        }

        if (strlen($sender) > 25 || in_array($sender, CONFIG_ADMIN) || in_array($sender, CONFIG_ROBOT)) {
            return;
        } elseif ($sender) {
            $filePath = APP_DIR_CONFIG . "user.blockList.txt";

            $allBlockList = file_get_contents($filePath);
            $allBlockList ? $blockSender = $allBlockList . "," . $sender : $blockSender = $sender;

            if (strpos($allBlockList, $sender) > -1) {
                $ret = "已存在";
            } else {
                file_put_contents($filePath, $blockSender);

                $ret = "添加成功";
            }
        } else {
            $ret = "添加黑名单异常";
        }

        return $ret;
    }

    /**
     *
     * 删除黑名单
     *
     */
    function deleteBlockList($msgSender)
    {
        $filePath = APP_DIR_CONFIG . "user.blockList.txt";

        $allBlockList = file_get_contents($filePath);
        $newBlockList = str_replace("," . $msgSender, "", $allBlockList);
        $newBlockList = str_replace($msgSender, "", $newBlockList);

        file_put_contents($filePath, $newBlockList);

        $ret = "删除成功";

        return $ret;
    }

    /**
     *
     * 获取黑名单
     *
     */
    function getBlockList()
    {
        $filePath = APP_DIR_CONFIG . "user.blockList.txt";

        $allBlockList = file_get_contents($filePath);
        $allBlockList ? $blockNum = count(explode(",", $allBlockList)) : $blockNum = 0;

        $ret = "列表如下:\n";
        $ret .= $allBlockList . "\n";
        $ret .= "共计【{$blockNum}】个";

        return $ret;
    }

    /**
     *
     * 加群
     *
     */
    function appInviteInGroup($msgRobot, $msgSource, $msgSender)
    {
        if (FRAME_ID == 10000) {
            $newData = "Api_JoinGroup('{$msgRobot}','{$msgSource}','')";

            $this->appSend($msgRobot, 1, $msgSource, $msgSender, NULL, array("msgType" => "json_msg", "msgOrigMsg" => $newData));
        } elseif (FRAME_ID == 20000) {
            $newDataType = 311;

            $newData = array();
            $newData['type'] = $newDataType;
            $newData['robot_wxid'] = $msgRobot;
            $newData['group_wxid'] = $msgSource;
            $newData['friend_wxid'] = $msgSender;

            $this->appSend($msgRobot, $newDataType, $msgSource, $msgSender, NULL, array("msgType" => "json_msg", "msgOrigMsg" => $newData));
        } else {
            return;
        }
    }

    /**
     *
     * 群互联
     *
     */
    function appGcInterconnected($msgRobot, $msgType, $msgSource, $msgSender, $msgContent)
    {
        $interconnected_1 = array();
        $interconnected_2 = array();

        $interconnectedSearch_1 = array_search($msgSource, $interconnected_1);
        $interconnectedSearch_2 = array_search($msgSource, $interconnected_2);

        $group = NULL;
        if ($interconnectedSearch_1 > -1) {
            $group = $interconnected_2[$interconnectedSearch_1];
        }

        if ($interconnectedSearch_2 > -1) {
            $group = $interconnected_1[$interconnectedSearch_2];
        }

        if ($group && !strpos($msgContent, '{"') && !strpos($msgContent, 'xml')) {
            $ret = "群({$group})成员({$msgSender}):\n" . $msgContent;

            $this->appSend($msgRobot, $msgType, $group, $msgSender, $ret);
        }
    }
}