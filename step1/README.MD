# 第一步

## 目标

文件浏览器

## 目标

使用 GET 方法 列出当前目录或者配置目录的内容，类似 nginx  index 功能。

## 需要实现的功能点

- [x] tcp 交互 (socket/bind/listen/accept)
- [x] 解析 http request
- [x] 处理 URL 路由
- [x] 响应 http response

注：
不考虑过于复杂的东西，只实现 GET


## HTTP 流程

![http 流程](../static/httpd_proc.jpg)


## 说明

*学习性质，不要在任何服务器上对公网部署！！！*

第一次写 socket 程序，所以是想到哪里写到哪里，所以代码看起来千奇百怪。下一步会进行整理。


## 运行

在当前目录执行 `php -f server.php`

简单配置如下（直接修改 server.php 即可）

```php
<?php
define("HTTP_IP", "127.0.0.1");     // 绑定 ip
define("HTTP_PORT", "9876");        // 端口
define("ROOT_PATH", "/");   // 指定浏览目录
```