<?php

namespace Tests\Controllers\Middlewares;

use Controllers\Router;
use PHPUnit\Framework\TestCase;
use Controllers\Middleware\MethodMiddleware;

class RouterMiddlewareTest extends TestCase
{
    private $middleware;
    private $router;

     public function setUp():void
    {
        $this->middleware = new MethodMiddleware();
        $this->router=new Router();
    }

    public function testGenericUri(){
        $this->router->get('/blog', $this->middleware,'posts');
        $this->router->get('/blog/{slug:[a-z0-9\-]+}-{id:\d+}', $this->middleware,'post.show');
        $uri=$this->router->generateUri('post.show',['slug'=>'mon-article','id'=>18]);
        $this->assertEquals('/blog/mon-article-18',$uri);
    }
}