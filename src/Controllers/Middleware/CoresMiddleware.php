<?php

namespace Controllers\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CoresMiddleware implements MiddlewareInterface
{
    /**
     * process
     *
     * @param  ServerRequestInterface $request
     * @param  RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        return $handler($request)->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS');
    }
}
