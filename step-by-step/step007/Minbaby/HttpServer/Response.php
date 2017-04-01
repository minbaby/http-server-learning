<?php

namespace Minbaby\HttpServer;

use Symfony\Component\HttpFoundation\Response as sResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
    public function __construct($code, $message, $protocol, $content, ResponseHeaderBag $headers = null)
    {
        $this->protocol = $protocol;
        $this->code = $code;
        $this->message = $message;
        $this->headers = $headers;
        $this->content = $content;

        if (!empty($this->headers)) {
            $this->headers->set('X-Server', "minbaby/0.0.7");
            $this->headers->set('Content-Length', strlen($content));
            $this->headers->set('Content-Type', 'text/html; charset=utf-8');
        }
    }

    public static function fromSymfonyResponse(sResponse $response)
    {
        return new static(
            $response->getStatusCode(),
            sResponse::$statusTexts[$response->getStatusCode()],
            $response->getProtocolVersion(),
            $response->getContent(),
            $response->headers
        );
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
        $ret = sprintf("HTTP/%s %s %s", $this->protocol, $this->code, $this->message);
        !empty($this->headers) && $ret .= CRLF . $this->headers->__toString(); // header
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
