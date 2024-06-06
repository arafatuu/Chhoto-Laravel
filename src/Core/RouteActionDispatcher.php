<?php

namespace App\Core;

class RouteActionDispatcher
{
    public $route;

    public $container;

    public function __construct(Route $route)
    {
        $this->route = $route;
        $this->container = new Container();
    }

    public function dispatch()
    {
        $action = $this->route->getAction();
        $params = $this->route->getParams();

        if (is_array($action)) {
            list($class, $method) = $action;
            $deps = $this->container->resolveDeps($method, $params, $class);
            call_user_func_array([$class, $method], $deps);
        } elseif (is_callable($action)) {
            $deps = $this->container->resolveDeps($action, $params);
            call_user_func_array($action, $deps);
        }
    }
}
