let ws = require("ws");
let axios = require("axios");

let botInfo = {
    id: "",
    name: "",
    token: "",
    type: 1, //0 私域 1 公域
    sessionId: null
};

/**
 *
 * 订阅多个事件 1 << 10 | 1 << 30
 *
 * @link https://bot.q.qq.com/wiki/develop/api/gateway/intents.html
 */
let authorization = "Bot " + botInfo.id + "." + botInfo.token,
    private = 1 << 9 | 1 << 28,
    public = 1 << 0 | 1 << 1 | 1 << 10 | 1 << 12 | 1 << 18 | 1 << 19 | 1 << 26 | 1 << 27 | 1 << 29 | 1 << 30,
    intents = botInfo.type == 0 ? (private | public) : public,
    shard = [0, 1];

let wsInfo = {
    s: null,
    op: -1,
    ret: "",
    connect: "",
    msgType: "",
    heartbeatInterval: 41250,
    wsUrl: "",
    gateway: "https://api.sgroup.qq.com/gateway/bot",
    postUrl: "http://localhost/bot.91m.top/app.php?frameId=70000&botType=" + botInfo.type
    //"http://bot-localhost.91m.top/app.php?frameId=70000"
    //本地需要 host 到 127.0.0.1，机器人需要 host 到 公网
}

let timerInfo = {
    botRun: "",
    botHeartbeat: "",
    timeout: 5000
}

axios({
    url: wsInfo.gateway,
    method: "GET",
    headers: {
        "Authorization": authorization
    }
}).then((res) => {
    let data = res.data;

    wsInfo.wsUrl = data.url;

    appLog("初始化", "获取地址 -> " + wsInfo.wsUrl);
    appLog("初始化", "建议分片 -> " + data.shards + " " + JSON.stringify(data.session_start_limit));
}).catch((err) => {
    console.error(err);
});

let botRun = function () {
    wsInfo.connect = new ws(wsInfo.wsUrl);
    wsInfo.connect.on("open", function () {
        appLog("连接打开", "等待指令");
    });

    wsInfo.connect.on("message", function (data) {
        let res = data.toString(),
            resJson = JSON.parse(res);

        wsInfo.op = resJson.op;
        wsInfo.s = resJson.s || "";
        wsInfo.t = resJson.t || "";

        resJson.s ? wsInfo.s = resJson.s : wsInfo.s = "";

        appLog("接收数据", "op:" + wsInfo.op + " s:" + wsInfo.s + " t:" + wsInfo.t + " d:" + res, "<-");

        /**
         *
         * opcode 的定义
         *
         * @link https://bot.q.qq.com/wiki/develop/api/gateway/opcode.html
         */
        if (wsInfo.op == 0) {
            if (wsInfo.t == "RESUMED") {
                appLog("连接恢复", "OK");

                //恢复成功之后，开始补发遗漏事件
            } else if (wsInfo.t == "READY") {
                appLog("连接成功", "获取 sessionId");

                botInfo.sessionId = resJson.d.session_id;
                //连接成功获取 sessionId
            } else if (["MESSAGE_CREATE", "AT_MESSAGE_CREATE", "DIRECT_MESSAGE_CREATE"].indexOf(wsInfo.t) > -1) {
                /**
                 *
                 * 私域机器人不用艾特
                 *
                 * @link https://wj.qq.com/s2/9379748/ed13
                 */
                new Promise((resolve, reject) => {
                    axios({
                        url: wsInfo.postUrl,
                        data: {
                            res: res,
                            authorization: authorization
                        },
                        method: "POST"
                    }).then((res) => {
                        //appLog("Body", res.data);

                        resolve(res.data)
                    }).catch((err) => {
                        reject(err);
                    });
                })
            }

            //服务端进行消息推送
        } else if (wsInfo.op == 7) {
            appLog("接收数据", "你还在吗?", "<-");

            //服务端通知客户端重新连接
        } else if (wsInfo.op == 9) {
            appLog("接收数据", "参数有误?", "<-");

            //当identify或resume的时候，如果参数有错，服务端会返回该消息
        } else if (wsInfo.op == 10) {
            wsInfo.heartbeatInterval = resJson.d.heartbeat_interval;

            if (botInfo.sessionId) {
                wsInfo.ret = {
                    "op": 6,
                    "d": {
                        "token": authorization,
                        "session_id": botInfo.sessionId,
                        "seq": wsInfo.s
                    }
                }

                appLog("发送数据", "尝试重连");
            } else {
                wsInfo.ret = {
                    "op": 2,
                    "d": {
                        "token": authorization,
                        "intents": intents,
                        "shard": shard,
                        "properties": {
                            "$os": "linux",
                            "$browser": "my_library",
                            "$device": "my_library"
                        }
                    }
                }

                appLog("发送数据", "尝试鉴权");
            }

            wsInfo.connect.send(appMsg(wsInfo.ret));

            /**
             *
             * 连接成功后开始发送心跳包
             *
             */
            clearInterval(timerInfo.botRun);
            clearInterval(timerInfo.botHeartbeat);

            timerInfo.botHeartbeat = setInterval(appHeartbeat, wsInfo.heartbeatInterval);

            //当客户端与网关建立ws连接之后，网关下发的第一条消息
        } else if (wsInfo.op == 11) {
            appLog("接收数据", "心跳 OK", "<-");

            //当发送心跳成功之后，就会收到该消息
        }
    });

    wsInfo.connect.on("close", function (err) {
        appLog("连接关闭", err);

        if (err != 4009) {
            botInfo.sessionId = null;

            appLog("连接关闭", "清除 sessionId");
        }

        clearInterval(timerInfo.botRun);
        clearInterval(timerInfo.botHeartbeat);

        timerInfo.botRun = setInterval(botRun, timerInfo.timeout);
    });

    wsInfo.connect.on("error", function (err) {
        appLog("连接错误", err);

        botInfo.sessionId = null;

        clearInterval(timerInfo.botRun);
        clearInterval(timerInfo.botHeartbeat);

        timerInfo.botRun = setInterval(botRun, timerInfo.timeout);
    });
}

function appLog(type, ret, direction = "->") {
    let ts = Math.round(new Date().getTime() / 1000).toString();

    console.log("[" + ts + "] " + type + " " + direction + " " + ret);
}

function appMsg(ret) {
    return JSON.stringify(ret);
}

function appHeartbeat() {
    wsInfo.ret = {
        "op": 1,
        "d": wsInfo.s
    };

    wsInfo.connect.send(appMsg(wsInfo.ret));

    appLog("发送数据", "心跳 OK");
}

setTimeout(() => {
    botRun();
}, 2500);