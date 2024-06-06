<?php

use App\Controllers\UserController;
use App\Core\Container;
use App\Core\Database;
use App\Core\Facade;
use App\Core\RouteActionDispatcher;
use App\Core\Router;
use App\Facade\DB;
use App\Facade\Route;
use App\Services\StorageService;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/helpers.php';

$container = new Container();

Facade::$app = $container;

$container->singleton('router', function (Container $container) {
    return $container->make(Router::class);
});

$container->bind('storage', function (Container $container) {
    return $container->make(StorageService::class);
});

$container->singleton('database', function (Container $container) {
    return $container->make(Database::class, ['localhost', 'laravel', 'root', '']);
});

Route::get('/', function () {
    echo '<pre>';
    // print_r(DB::table('tests')->find(2));
    print_r(DB::table('tests')->select('id', 'name')->where('name', 'Arafat')->where('id', '=', 1)->orWhere('id', 2)->orWhere('name', '=', 'Yeamin')->orderBy('id')->orderByDesc('name')->toSql());
    // print_r('Hello Developers..!');
    echo '</pre>';
});

Route::get('about/{id}', function (UserController $userController, $id) {
    echo $id;
});

Route::dispatch();



if (Route::isRouteMatched()) {
    $currentRoute = Route::getCurrentRoute();
    $routeActionDispatcher = new RouteActionDispatcher($currentRoute);
    $routeActionDispatcher->dispatch();
} else {
    http_response_code(404);
}
