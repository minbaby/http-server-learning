<?php

namespace Minbaby\HttpParse;

trait HeaderTrait
{
    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }
}
