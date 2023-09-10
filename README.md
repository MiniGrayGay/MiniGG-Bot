# 特性

* 支持多框架，一套代码多个平台
* 插件热更新，无需重新卸载安装
* 全局管理器，简单配置即可上手

# 框架

## 协议
> 回调地址:http://your.domain/app.php?frameId=10000&frameHost=127.0.0.1:1111&frameGc=123456 ，frameId 不填默认 50000，~~划线的项目~~ 已经停止维护

| frameId | 框架                                                                                                   | 平台     | 鉴权     | HTTP | WS |
|---------|--------------------------------------------------------------------------------------------------------|----------|----------|------|----|
| 2500    | [小米小爱开放平台](https://developers.xiaoai.mi.com)                                                    | 小爱音箱  | -       | ✓    | ✗  |
| 10000   | ~~[MyPCQQ](https://www.mypcqq.cc)~~                                                                    | 电脑 QQ  | 白名单IP | ✓    | ✗  |
| 15000   | ~~[OPQ](https://docs.opqbot.com)~~                                                                     | NTQQ     | -       | ✗    | ✓  |
| 20000   | [可爱猫](http://www.keaimao.com.cn/forum.php)                                                           | 微信     | 密钥    | ✓    | ✗  |
| 50000   | ~~[NOKNOK](https://bot-docs.github.io/pages/events/1_callback.html)~~                                  | NOKNOK   | 密钥    | ✓    | ✗  |
| 60000   | [go-cqhttp](https://github.com/Mrs4s/go-cqhttp/blob/master/docs/guild.md)                              | 手机 QQ  | 密钥     | ✓    | ✓  |
| 70000   | [QQ 机器人](https://qun.qq.com/qqweb/qunpro/share?_wv=3&_wwv=128&inviteCode=1d9lY8&from=181074&biz=ka) | QQ 频道  | 密钥     | ✗    | ✓  |
| 75000   | [QQ 机器人](https://qun.qq.com/qqweb/qunpro/share?_wv=3&_wwv=128&inviteCode=1d9lY8&from=181074&biz=ka) | QQ 群    | 密钥     | ✗    | ✓  |
| 80000   | ~~[王者营地](https://pvp.qq.com)~~                                                                         | 王者营地 | -        | ✓    | ✗  |
| 90000   | [米游社](https://webstatic.mihoyo.com/vila/bot/doc)                                                    | 米游社   | 密钥     | ✓    | ✗  |

## 回复
> **-** 表示不确定，且很大概率不行，**小爱音箱** 只有文本

| frameType     | 文本 | 图片 | at_msg | reply_msg | markdown_msg |
|---------------|------|------|--------|-----------|--------------|
| MyPCQQ        | ✓    | ✓    | ✓      | ✗         | ✗            |
| OPQ           | ✓    | ✓    | ✓      | ✗         | ✗            |
| 可爱猫        | ✓    | 本地 | ✓      | ✗         | ✗            |
| NOKNOK 机器人 | ✓    | ✗    | ✓      | ✓         | ✓            |
| go-cqhttp     | ✓    | ✓    | ✓      | ✓         | ✗            |
| QQ 频道       | ✓    | ✓    | ✓      | ✓         | ✗            |
| QQ 群         | ✓    | ✗    | ✓      | ✗         | ✗            |
| 王者营地      | ✓    | ✗    | ✓      | ✗         | ✗            |
| 米游社        | ✓    | ✓    | ✓      | ✓         | ✗            |

# 配置

## redis

数据缓存，关键词触发、统计都需要 [立即下载](https://redis.io/download)。

## 密钥

所有带 **example.** 前缀的都需要自行配置。里面的密钥换成自己的，然后去掉 **example.** 即可。

```
app/example.config 內的文件修改完以后复制一份到 app/config
app/database/example.app.sql.php
app/ws/example.qq_ws.js
```

## frameHost
> **HTTP** 转发回去的 Host，默认为 **app/config/app.config.php** 中配置的 Host

如需外网访问，建议 服务器端防火墙、安全策略组 放通 **8000-8100** 端口。

## frameGc

NOKNOK 和 QQ 频道 请填 **子频道ID** ，不填默认全部处理。

## 插件

安装其他插件可以参考 [demo](https://jmglsi.coding.net/public/bot.91m.top/plugin_demo/git/files) 插件的说明。

建议插件使用 **plugin_** 作为前缀，本地文件夹与插件名保持一致。

# 使用

## 小爱音箱

[创建技能](https://developers.xiaoai.mi.com/skills/create/list) -> 编辑技能 -> 配置服务 -> 配置信息，按提示配置即可

## 可爱猫

[百度网盘](https://pan.baidu.com/s/1f1vk49VvCOLSzKqrUSQOzw) 提取码: vivk。

## QQ 频道 - 官方

根目录下执行以下命令安装依赖并运行:

```
yarn

yarn start:qq
```

## QQ 频道 - 第三方

根目录下 **config.yml** post 的下方加入以下信息:

```
- url: 'http://your.domain/app.php?frameId=60000'
  secret: '' #密钥
```

## 王者营地

需要自行写个后端，拉取子频道內的最新消息推送给框架，Content-Type 为 json，frameId 为 80000

## ~~MyPCQQ~~
> 官方不再维护

根目录下 **Set.ini** 的底下加入以下信息，按照 log 填入白名单 IP，每个空格分开。

```
[tran]
enable=1
target=http://your.domain/app.php?frameId=10000
whitelist=127.0.0.1 1.1.1.1
```

## ~~NOKNOK~~
> 官方不再维护

找管理员申请，需要注意的是 NOKNOK 的回调地址不允许带参数，默认 frameId 为 50000 NOKNOK 的地址。

# 写在最后

环境密钥配置好以后需要 **管理员** 向机器人发送 **功能** 初始化插件，之后每次增删插件也需要。

只有注册过的命令下次才会调用相关插件，不用每一句话都轮询所有插件了。

## 注意

路径、文件必须有写入、读取权限，否则【缓存】、【发图】相关功能受到影响。

如果有两个相似的命令 (比如 **一言状态** 和 **一言** )，建议长的放短的前面，否则调用次数的统计可能会统计到先匹配到的关键词上。