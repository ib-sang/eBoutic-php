<?php 

namespace App\Services\Role;

use App\Services\Role\Actions\RoleCrudAction;
use Controllers\Module;
use Controllers\Router;
use Psr\Container\ContainerInterface;

class RoleModule extends Module
{

    const DEFINITIONS = __DIR__.'/config.php';
    const MIGRATIONS = __DIR__.'/db/migrations';
    const SEEDS = __DIR__.'/db/seeds';

    public function __construct(ContainerInterface $container, Router $router)
    {
        $prefix = $container->get('role.prefix');
        // admin
        $router->crud("$prefix", RoleCrudAction::class, "role");
    }
}
