<?php

namespace App\Controllers;

use App\Services\UserService;

class UserController
{
    public function __construct(UserService $userService)
    {
        
    }

    public function index($username, UserService $userService, $slug = null)
    {
        return $username . '-' .$slug;
    }
}
