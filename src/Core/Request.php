<?php

namespace App\Core;

class Request
{
    public $server = [];

    public function __construct()
    {
        $this->server = $_SERVER ?? [];
    }

    public function getUri()
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }
}
