<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

function addRoute(RouteCollection $collection)
{
    $collection->add(
        'home',
        new Route(
            '/a',
            [
                '_controller' => 'Minbaby\Controller\IndexController',
                '_method' => 'show'
            ],
            [],
            [],
            '',
            [],
            ["GET"],
            ''
        )
    );
}
