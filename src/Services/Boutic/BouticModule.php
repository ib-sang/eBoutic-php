<?php 

namespace App\Services\Boutic;

use App\Services\Boutic\Actions\BouticCrudAction;
use Cake\Core\ContainerInterface;
use Controllers\Module;
use Controllers\Router;

class BoutisModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';
    const MIGRATIONS = __DIR__.'/db/migrations';
    const SEEDS = __DIR__.'/db/seeds';

    public function __construct(ContainerInterface $container, Router $router)
    {
        $prefix = $container->get('enterprise.prefix');
        // admin
        $router->crud("$prefix", BouticCrudAction::class, "boutics");
    }
}