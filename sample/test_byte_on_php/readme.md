# 测试 php 处理 socket 中的 byte

## 目标

使用 socket 系列的函数， 进行 byte 数据发送，接受等相关处理。


## 自定义协议

长度+响应/请求+字符串 =》 [10/][1/2][abcdefgqdf]

// TODO 发送图片