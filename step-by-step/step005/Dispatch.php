<?php

namespace Minbaby;

use Minbaby\HttpParse\Request;
use Minbaby\HttpParse\Response;

class Dispatch
{
    public function run(Request $request)
    {
        // 这里应该有一个 map， 这里暂时只处理 index
        $response = (new IndexController())->show($request);

        if (!($response instanceof Response)) {
            $headers = ['X-Server' => 'minbaby/0.0.5', 'X-A' => "B"];
            $response = new Response(200, 'Ok', 'HTTP/1.1', $response, $headers);
        }

        return $response->__toString();
    }
}
