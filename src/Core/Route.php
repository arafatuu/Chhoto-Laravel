<?php

namespace App\Core;

class Route
{
    private $uri;

    private $method;

    private $pattern;

    private $action;

    private $params = [];

    public function __construct($method, $uri, $action)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->action = $action;

        $this->setPattern();
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    private function setPattern()
    {
        $pattern = [
            '/\{([\w]+)\}/' => fn () => '([\w\-\_]+)',
            '/\{([\w]+\?)\}/' => fn () => '?([\w\-\_]+)?',
        ];

        $this->pattern = sprintf('@^/?%s/?$@i', preg_replace_callback_array($pattern, $this->uri));
    }

    public function __toString()
    {
        return $this->uri;
    }
}
