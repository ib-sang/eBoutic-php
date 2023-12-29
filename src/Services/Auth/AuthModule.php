<?php

namespace App\Services\Auth;

use App\Services\Auth\Actions\LoginAttemptAction;
use App\Services\Auth\Actions\LogoutAttemptAction;
use App\Services\Auth\Actions\RegisterAttemptAction;
use Controllers\Module;
use Controllers\Router;
use Psr\Container\ContainerInterface;
use App\Services\Auth\Actions\UserAction;

class AuthModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';
    const MIGRATIONS = __DIR__.'/db/migrations';
    const SEEDS = __DIR__.'/db/seeds';

    public function __construct(ContainerInterface $container, Router $router)
    {
        $prefix = $container->get('auth.prefix');
        
        // register
        $router->post("$prefix/register", RegisterAttemptAction::class);
        // // login
        $router->post("$prefix/login", LoginAttemptAction::class);
        // user
        $router->get("$prefix/user", UserAction::class, 'auth.user');
        // sign out
        $router->get("$prefix/signout/{id:\d+}", LogoutAttemptAction::class, 'user.signout');
    }
}
