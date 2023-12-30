<?php 

namespace App\Services\History;

use App\Services\History\Actions\StatusIndexAction;
use Controllers\Module;
use Controllers\Router;
use Psr\Container\ContainerInterface;

class HistoryModule extends Module{

    const DEFINITIONS = __DIR__.'/config.php';
    const MIGRATIONS = __DIR__.'/db/migrations';
    const SEEDS = __DIR__.'/db/seeds';

    public function __construct(ContainerInterface $container, Router $router)
    {
        $prefix = $container->get('history.prefix');
        // get status
        $router->get("$prefix", StatusIndexAction::class, "history.index");
    }


}