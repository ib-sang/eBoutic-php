<?php

namespace App\Services\Users;

use Controllers\Module;
use Controllers\Router;
use Psr\Container\ContainerInterface;
use App\Services\Users\Actions\CrudUserAction;
use App\Services\Users\Actions\CrudProfileAction;

class UsersModule extends Module
{
    
    const DEFINITIONS = __DIR__.'/config.php';
    const MIGRATIONS = __DIR__.'/db/migrations';
    const SEEDS = __DIR__.'/db/seeds';


    public function __construct(ContainerInterface $container, Router $router)
    {
        $prefix = $container->get('user.prefix');
        
        // crud user
        $router->crud("$prefix", CrudUserAction::class, 'users');
        $router->crud("$prefix/profile", CrudProfileAction::class, 'users.profile');
    }
}
