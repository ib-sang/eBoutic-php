<?php

use Controllers\Middleware\CsrfMiddleware;
use Controllers\Renderer\RendererInterface;
use Controllers\Renderer\TwigRendererFactory;
use Controllers\Router;
use Controllers\Router\RouterTwigExtension;
use Controllers\Session\PHPSession;
use Controllers\Session\SessionInterface;
use Controllers\Twig\CsrfExtension;
use Psr\Container\ContainerInterface;

return [
    'database.host'=>'127.0.0.1',
    'database.username'=>'c1915939c_eboutic',
    'database.password'=>'eboutic@2024',
    'database.name'=>'c1915939c_eboutic',
    'views.path'=> dirname(__DIR__).'/views',
    'twig.extensions' => [
        \DI\get(RouterTwigExtension::class),
        \DI\get(CsrfExtension::class)
    ],
    Router::class => \DI\create(),
    SessionInterface::class=>\DI\create(PHPSession::class),
    CsrfMiddleware::class => \DI\autowire()->constructor(\DI\get(SessionInterface::class)),
    RendererInterface::class => \DI\factory(TwigRendererFactory::class),
    \PDO::class=> function(ContainerInterface $c){
        return new PDO("mysql:host=".$c->get('database.host').';dbname='.$c->get('database.name'),
                $c->get('database.username'),
                $c->get('database.password'),
            [
                PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_OBJ,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        },
        
    
    ];