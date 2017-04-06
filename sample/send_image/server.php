<?php

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if ($socket === false) {
    $errorCode = socket_last_error($socket);
    $errorMessage = socket_strerror($errorCode);
    echo sprintf("(%s) %s", $errorCode, $errorMessage);
    die(-1);
}

$flags = socket_bind($socket, '127.0.0.1', 9876);

if ($flags == false) {
    $errorCode = socket_last_error($socket);
    $errorMessage = socket_strerror($errorCode);
    echo sprintf("(%s) %s", $errorCode, $errorMessage);
    die(-2);
}

$flags = socket_listen($socket, 1);
if ($flags == false) {
    $errorCode = socket_last_error($socket);
    $errorMessage = socket_strerror($errorCode);
    echo sprintf("(%s) %s", $errorCode, $errorMessage);
    die(-2);
}

// 这里如果 accept 之后，是可以无限 get 数据的，直到 socket close
// 需要考虑一个问题， 如果三个包一起发来， 那就要处理这个问题了！！！！
while (($acceptSocket = socket_accept($socket)) !== false) {
    // 基本思路， 从 socket 读取定义的头长度， 然后再从 socket 中读取定义的数据长度。
    // 如果 读不到想要的长度呢？
    $socketBuffer = new SocketBuf();
    $socketBuffer->reset();
    while (true) {
        $buf = socket_read($acceptSocket, 1024);

        if (false === $buf) {
            $errorCode = socket_last_error($socket);
            $errorMessage = socket_strerror($errorCode);
            echo sprintf("(%s) %s", $errorCode, $errorMessage);
            die(-2);
        }

        if (strlen($buf) == 0) {
            // 读不到数据
            echo "读不到数据", PHP_EOL;
            echo sprintf("len-%s\n", strlen($buf));
            break;
        }

        $socketBuffer->add($buf);

        while (!empty($body = $socketBuffer->getBody())) {
            file_put_contents('/tmp/test.jpg', $body);
        }
    }
    echo "Done...", PHP_EOL;
}

socket_close($socket);

class SocketBuf
{
    private $headerSize = 4;

    private $header = 0;

    private $body = [];

    private $buffer = "";

    const HEADER_LEN = 4;

    public function add($buf)
    {
        echo "+ => " . $buf, PHP_EOL;

        $this->buffer .= $buf;

        if (strlen($this->buffer) >= self::HEADER_LEN && $this->header === 0) {
            $headerData = substr($this->buffer, 0, self::HEADER_LEN);
            $unpack = unpack('isize/', $headerData);// 截取需要的数据，进行 unpack
            $this->header = $unpack['size'];
        } else {
            if (strlen($this->buffer) > $this->header + self::HEADER_LEN) {
                // 这个。。是缓冲内存多于一个包长度
                $this->body[] = substr($this->buffer, self::HEADER_LEN, $this->header);// 先解析一个包
                $this->buffer = substr($this->buffer, $this->header + self::HEADER_LEN);

                echo '+', $this->buffer, "==>buffer", PHP_EOL;

                // 解析剩下的可能的包
                while (strlen($this->buffer) >= $this->header + self::HEADER_LEN) {
                    $headerData = substr($this->buffer, 0, self::HEADER_LEN);
                    $unpack = unpack('isize/', $headerData);
                    $this->header = $unpack['size'];
                    $this->body[] = substr($this->buffer, self::HEADER_LEN, $this->header);// 先解析一个包
                    $this->buffer = substr($this->buffer, $this->header + self::HEADER_LEN);
                }
            } elseif (strlen($this->buffer) < $this->header + self::HEADER_LEN) {
                // 包还没有完成 不处理
            } else {
                // 刚好接受到需要的数据
                $this->body[] = substr($this->buffer, self::HEADER_LEN);
            }
        }
    }

    public function reset()
    {
        $this->header = 0;
        $this->body = [];
        $this->buffer = "";
    }

    public function getBody()
    {
        return array_shift($this->body);
    }
}
