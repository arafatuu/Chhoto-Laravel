<?php

namespace App\Core;

class Router
{
    private $request;

    private $currentRoute;

    private $routes = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function addRoute($method, $uri, $action)
    {
        $this->routes[] = new Route($method, $uri, $action);

        return $this;
    }

    public function get($uri, $action)
    {
        return $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    public function isRouteMatched()
    {
        return $this->currentRoute instanceof Route;
    }

    public function dispatch()
    {
        $uri = $this->request->getUri();

        /**
         * @var Route $route
         */
        foreach ($this->routes as $route) {
            if (preg_match($route->getPattern(), $uri, $matches)) {
                $params = array_slice($matches, 1);
                $route->setParams($params);
                $this->currentRoute = $route;
                break;
            }
        }

        return $this;
    }
}
