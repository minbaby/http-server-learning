<?php

namespace Minbaby;

use Minbaby\HttpParse\Request;
use Minbaby\HttpParse\Response;

class IndexController
{
    public function show(Request $request)
    {
        $content = json_encode($_SERVER);
        $response = new Response(200, "Ok/t", "HTTP/1.1", $content, null);
        return $response;
    }
}
