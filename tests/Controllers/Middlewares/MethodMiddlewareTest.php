<?php

namespace Tests\Controllers\Middlewares;

use Controllers\Middleware\MethodMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class MethodMiddlewareTest extends TestCase
{
    private $middleware;

     public function setUp():void
    {
        $this->middleware = new MethodMiddleware();
    }

    public function testMethod()
    {
        $middle=MethodMiddleware::class;
        $request=(new ServerRequest('DELETE','/demo'))
                ->withParsedBody(['_method'=>'POST']);
        call_user_func_array(function(ServerRequestInterface $request){
            $this->assertEquals('DELETE',$request->getMethod());
        }, [$request]
        );
    }
}