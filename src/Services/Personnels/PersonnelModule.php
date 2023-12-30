<?php

namespace App\Services\Personnels;

use App\Services\Personnels\Actions\PersonnelCrudAction;
use App\Services\Personnels\Actions\PersonnelInitActionIndex;
use App\Services\Personnels\Actions\PersonnelInitActionPost;
use Controllers\Module;
use Controllers\Router;
use Psr\Container\ContainerInterface;

class PersonnelModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';
    const MIGRATIONS = __DIR__.'/db/migrations';
    const SEEDS = __DIR__.'/db/seeds';

    public function __construct(ContainerInterface $container, Router $router)
    {
        $prefix = $container->get('personnel.prefix');
        // admin
        $router->crud("$prefix", PersonnelCrudAction::class, "personnels");
        $router->get("$prefix/init/{id:\d+}", PersonnelInitActionIndex::class, "personnels.init");
        $router->post("$prefix/init/{id:\d+}", PersonnelInitActionPost::class);
    }
}
