<?php 

namespace App\Services\Product;

use App\Services\Product\Actions\ProductCrudAction;
use App\Services\Product\Actions\StockCrudAction;
use Controllers\Module;
use Controllers\Router;
use Psr\Container\ContainerInterface;

class ProductModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';
    const MIGRATIONS = __DIR__.'/db/migrations';
    const SEEDS = __DIR__.'/db/seeds';

    public function __construct(ContainerInterface $container, Router $router)
    {
        $prefix = $container->get('product.prefix');
        // admin products
        $router->crud("$prefix", ProductCrudAction::class, "products");
        
        // admin products
        $router->crud("$prefix/stocks", StockCrudAction::class, "products.stock");
    }
}