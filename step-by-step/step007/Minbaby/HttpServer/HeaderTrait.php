<?php

namespace Minbaby\HttpServer;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait HeaderTrait
{
    /**
     * @var \Symfony\Component\HttpFoundation\ResponseHeaderBag
     */
    protected $headers;

    /**
     * @return \Symfony\Component\HttpFoundation\ResponseHeaderBag
     */
    public function getHeaders()
    {
        if (empty($this->headers)) {
            $this->headers = new ResponseHeaderBag();
        }
        return $this->headers;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ResponseHeaderBag $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
}
