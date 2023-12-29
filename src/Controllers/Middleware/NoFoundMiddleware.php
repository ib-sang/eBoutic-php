<?php

namespace Controllers\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NoFoundMiddleware implements MiddlewareInterface
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
        return new Response(
            404,
            ['headers'=>'Content-Type: application/json'],
            json_encode(['message' =>"dosn't link for api", "status" => 404])
        );
    }
}
