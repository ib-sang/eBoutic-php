<?php

use App\Services\Auth\ForbiddenMiddleware;
use Controllers\Auth;
use Controllers\Auth\User;

return [
    'auth.prefix' => '/api/v1',
    User::class=>\DI\factory(function (Auth $auth) {
        return $auth->getUser();
    })->parameter('auth', \DI\get(Auth::class)),
    Auth::class=> \DI\get(\App\Services\Auth\DatabaseAuth::class),
    ForbiddenMiddleware::class=>\DI\autowire()->constructorParameter('loginPath', \DI\get('auth.login'))
];
