<?php

namespace App\Services\Enterprise;

use App\Services\Enterprise\Actions\EnterpriseCrudAction;
use App\Services\Enterprise\Actions\UploadLogoAction;
use App\Services\Enterprise\Actions\UploadLogoGetAction;
use Controllers\Module;
use Controllers\Router;
use Psr\Container\ContainerInterface;

class EnterpriseModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';
    const MIGRATIONS = __DIR__.'/db/migrations';
    const SEEDS = __DIR__.'/db/seeds';

    public function __construct(ContainerInterface $container, Router $router)
    {
        $prefix = $container->get('enterprise.prefix');
        // admin
        $router->crud("$prefix", EnterpriseCrudAction::class, "enterprise");
        $router->get("$prefix/upload/{id:\d+}", UploadLogoGetAction::class, "enterprise.upload");
        $router->post("$prefix/upload/{id:\d+}", UploadLogoAction::class);
    }
}
