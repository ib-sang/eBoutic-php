<?php declare(strict_types=1);

namespace Tests\Controllers\Middlewares;

use Controllers\Router;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Controllers\Middleware\MethodMiddleware;

class NoFoundMiddlewareTest extends TestCase
{
    private $middleware;

    private $router;

     public function setUp():void
    {
        $this->middleware = new MethodMiddleware();
        $this->router = new Router();
    }

    public function testGetMethodIfURLDoesNotExists()
    {
        $request=new ServerRequest('GET','/blog');
        $this->router->get('/blogeez', $this->middleware,'blog');
        $route=$this->router->match($request);
        $this->assertEquals(null,$route);
    }
}