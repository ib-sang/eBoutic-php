<?php

use Controllers\Middleware\MethodMiddleware;
use Controllers\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private $router;

    private $middleware;

    public function setUp():void
    {
        $this->router = new Router();
        $this->middleware = new MethodMiddleware();
    }

    public function testGetMethodWithParameters(){
        $request=new ServerRequest('GET','/blog/mon-slug-8');
        $this->router->get('/blog', $this->middleware,'posts');
        $this->router->get('/blog/{slug:[a-z0-9\-]+}-{id:\d+}', $this->middleware,'post.show');
        $route=$this->router->match($request);
        $this->assertEquals('post.show',$route->getName());
        $this->assertEquals(['slug'=>'mon-slug','id'=>8],$route->getParams());
        //test invalid url 
        $route=$this->router->match(new ServerRequest('GET','/blog/mon_slug-8'));
        $this->assertEquals(null,$route);
    }

    public function testGenericUriWithQueryParams(){
        $this->router->get('/blog', $this->middleware,'posts');
        $this->router->get('/blog/{slug:[a-z0-9\-]+}-{id:\d+}', $this->middleware,'post.show');
        $uri=$this->router->generateUri('post.show',
                        ['slug'=>'mon-article', 'id'=>18],
                        ['p'=>2]
        );
        $this->assertEquals('/blog/mon-article-18?p=2',$uri);
    }
}