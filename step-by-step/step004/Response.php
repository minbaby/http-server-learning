<?php

namespace Minbaby\HttpParse;

class Response
{
    use HeaderTrait;

    protected $protocol;

    protected $code;

    protected $message;

    private $content;

    /**
     * Response constructor.
     *
     * @param $protocol
     * @param $code
     * @param $message
     * @param $headers
     */
    public function __construct($code, $message, $protocol, $content, array $headers)
    {
        $this->protocol = $protocol;
        $this->code = $code;
        $this->message = $message;
        $this->headers = $headers;
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param mixed $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function __toString()
    {
        $ret = sprintf("%s %s %s", $this->protocol, $this->code, $this->message);
        !empty($this->headers) && $ret .= CRLF . $this->implodeKeyValue($this->headers); // header
        !empty($this->content) && $ret .= CRLF . $this->content; // content
        return $ret;
    }

    private function implodeKeyValue($data)
    {
        $str = '';
        foreach ($data as $key => $value) {
            $str .= sprintf("%s:%s " . CRLF, $key, $value);
        }

        return $str;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
