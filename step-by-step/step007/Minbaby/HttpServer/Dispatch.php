<?php

namespace Minbaby\HttpServer;

use Symfony\Component\HttpFoundation\Request as sRequest;
use Symfony\Component\HttpFoundation\Response as sResponse;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Dispatch
{
    public function __construct()
    {
        include __DIR__ . '/../../routes.php';
    }

    private function parseAndMath(Request $request)
    {
        $routes = new RouteCollection();
        addRoute($routes);

        $context = new RequestContext('/');
        $match = new UrlMatcher($routes, $context);

        $arr = $match->match($request->getPath());

        return $routes->get(array_get($arr, '_route'));
    }

    public function run(Request $request)
    {
        /** @var sResponse $response */
        $response = null;
        try {
            $route = $this->parseAndMath($request);
            $c = $route->getDefault('_controller');
            $m = $route->getDefault('_method');

            $request = sRequest::create($request->getPath(), $request->getMethod()); // todo

            $request = new sRequest();

            $response = (new $c)->$m($request);
            if (!($response instanceof sResponse)) {
                if (is_array($response)) {
                    $response = json_encode($response);
                }
                $response = sResponse::create($response, 200);
            }
        } catch (\Exception $exception) {
            $response = sResponse::create($exception->getMessage(), 503);
        }

        return (Response::fromSymfonyResponse($response))->__toString();
    }
}
