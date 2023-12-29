<?php

namespace Tests\Controllers\Middlewares;

use Controllers\Router;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Controllers\Middleware\MethodMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class TrailingSlashMiddlewareTest extends TestCase
{

    private $middleware;

    private $router;

     public function setUp():void
    {
        $this->middleware = new MethodMiddleware();
        $this->router=new Router();
    }

    public function testRoutePrefix()
    {
        $request=new ServerRequest('GET','/blog/');
        $this->router->get('/blog', $this->middleware,'blog');
        call_user_func_array(function(ServerRequestInterface $request){
            $this->assertEquals('/blog',$this->router->generateUri('blog'));
        }, [$request]
        );
    }
    
}