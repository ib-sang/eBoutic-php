<?php 

namespace App\Services\Category;

use App\Services\Category\Actions\CategoryCrudAction;
use Controllers\Module;
use Controllers\Router;
use Psr\Container\ContainerInterface;

class CategoryModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';
    const MIGRATIONS = __DIR__.'/db/migrations';
    const SEEDS = __DIR__.'/db/seeds';

    public function __construct(ContainerInterface $container, Router $router)
    {
        $prefix = $container->get('category.prefix');
        // admin
        $router->crud("$prefix", CategoryCrudAction::class, "categories");
    }
}