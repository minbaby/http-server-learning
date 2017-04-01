<?php

namespace Minbaby\Controller;

use Minbaby\HttpServer\Request;

class IndexController
{
    public function show(Request $request)
    {
        return $_SERVER;
    }
}
