<?php

use App\Modules\Home\HomeModule;
use App\Services\Admin\AdminModule;
use App\Services\Agences\AgenceModule;
use App\Services\Auth\AuthModule;
use App\Services\Busies\BusiesModule;
use App\Services\Cartes\CarteModule;
use App\Services\Cashies\CashModule;
use App\Services\Circuits\CircuitModule;
use App\Services\Cities\CityModule;
use App\Services\Collies\CollieModule;
use App\Services\Dashboard\DashModule;
use App\Services\Depences\DepenceModule;
use App\Services\Deplacements\DeplacementModule;
use App\Services\Enterprise\EnterpriseModule;
use App\Services\Guichets\GuichetModule;
use App\Services\History\HistoryModule;
use App\Services\Locations\LocationsModule;
use App\Services\Notifies\NotifyModule;
use App\Services\Personnels\PersonnelModule;
use App\Services\Posts\PostsModule;
use App\Services\Reservations\ReservationModule;
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
->addModule(AdminModule::class)
->addModule(EnterpriseModule::class)
->addModule(RoleModule::class)
->addModule(AgenceModule::class)
->addModule(BusiesModule::class)
->addModule(GuichetModule::class)
->addModule(CityModule::class)
->addModule(CarteModule::class)
->addModule(CircuitModule::class)
->addModule(DeplacementModule::class)
->addModule(ReservationModule::class)
->addModule(CollieModule::class)
->addModule(PersonnelModule::class)
->addModule(DepenceModule::class)
->addModule(DashModule::class)
->addModule(PostsModule::class)
->addModule(CashModule::class)
->addModule(NotifyModule::class)
->addModule(LocationsModule::class)
->addModule(HistoryModule::class)

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
