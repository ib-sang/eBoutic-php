<?php

namespace Tests\Controllers\Middlewares;

use Controllers\Middleware\MethodMiddleware;
use Controllers\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RoutePrefixMiddlewareTest extends TestCase
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
        $request=new ServerRequest('GET','/blog');
        $this->router->get('/blog', $this->middleware,'posts');
        $this->router->get('/blog/{slug:[a-z0-9\-]+}-{id:\d+}', $this->middleware,'post.show');
        $uri=$this->router->generateUri('post.show',['slug'=>'mon-article','id'=>18]);
        $this->assertEquals('/blog/mon-article-18',$uri);

        ;
        call_user_func_array(function(ServerRequestInterface $request){
            $path= $request->getUri()->getPath();
            $this->assertEquals('/blog', $path);
        }, [$request]);
    }
}