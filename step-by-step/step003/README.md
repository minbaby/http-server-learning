# 第三步

## 目标

多进程阻塞 http server, 实现 file index

最终会有一个 master 进程，N 个 woker 进程。

master accept , work 处理， 所以需要进程间通讯。

类似 NGINX 的方式， master listen，  worker accept
