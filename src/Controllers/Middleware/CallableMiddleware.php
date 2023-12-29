<?php

namespace Controllers\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use GuzzleHttp\Psr7\Response;

class CallableMiddleware implements MiddlewareInterface
{
    private $callable;

    public function __construct($callable)
    {
        $this->callable = $callable;
    }

    public function getCallback()
    {
        return $this->callable;
    }

     /**
     * process
     *
     * @param  ServerRequestInterface $request
     * @param  RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        return new Response();
    }
}
