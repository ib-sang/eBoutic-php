<?php

use App\Services\Auth\AuthModule;
use App\Services\Boutic\BouticModule;
use App\Services\Category\CategoryModule;
use App\Services\Enterprise\EnterpriseModule;
use App\Services\History\HistoryModule;
use App\Services\Personnels\PersonnelModule;
use App\Services\Product\ProductModule;
use App\Services\Role\RoleModule;
use App\Services\Users\UsersModule;
use Controllers\Middleware\CoresMiddleware;
use Controllers\Middleware\MethodMiddleware;
use Controllers\Middleware\NoFoundMiddleware;
use Controllers\Middleware\RouterMiddleware;
use Controllers\Middleware\TrailingSlashMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use Controllers\Middleware\DispatcherMiddleware;

require dirname(__DIR__)."/vendor/autoload.php";

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, charset=utf-8, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
    header("HTTP/1.1 200 OK");
//die();
}
$data = json_decode(file_get_contents("php://input"), true);

$app=(new \Controllers\App(dirname(__DIR__).'/config/config.php'))
->addModule(UsersModule::class)
// ->addModule(HomeModule::class)
->addModule(AuthModule::class)
// ->addModule(AdminModule::class)
->addModule(EnterpriseModule::class)
->addModule(RoleModule::class)
->addModule(PersonnelModule::class)
// ->addModule(DepenceModule::class)
// ->addModule(DashModule::class)
// ->addModule(PostsModule::class)
// ->addModule(CashModule::class)
// ->addModule(NotifyModule::class)
// ->addModule(LocationsModule::class)
->addModule(HistoryModule::class)
->addModule(BouticModule::class)
->addModule(CategoryModule::class)
->addModule(ProductModule::class)

    ;

$container=$app->getContainer();

$app->pipe(TrailingSlashMiddleware::class)
    ->pipe(MethodMiddleware::class)
    ->pipe(RouterMiddleware::class)
    ->pipe(DispatcherMiddleware::class)
    ->pipe(NoFoundMiddleware::class)
    ->pipe(CoresMiddleware::class)
    ;

    

if (php_sapi_name()!=='cli') {
    $response=$app->run(ServerRequest::fromGlobals());
    \Http\Response\send($response);
}
