<?php 

namespace App\Services\Product;

use App\Services\Product\Actions\ProductCrudAction;
use App\Services\Product\Actions\SaleItemListAction;
use App\Services\Product\Actions\SalesCrudAction;
use App\Services\Product\Actions\SaleStatusCrudAction;
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
        // admin products sales
        $router->crud("$prefix/sales", SalesCrudAction::class, "products.sales");
        // admin sale status 
        $router->crud("$prefix/statussales", SaleStatusCrudAction::class, "status.sale");

        $router->get("$prefix/saleitems/{id:\d+}", SaleItemListAction::class, "products.boutics");
    }
}