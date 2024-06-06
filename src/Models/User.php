<?php

namespace App\Models;

class User
{
    protected $timestamps = true;

    private $attributes = [];

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}
