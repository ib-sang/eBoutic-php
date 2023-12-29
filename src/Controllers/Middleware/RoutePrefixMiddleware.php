<?php

namespace Controllers\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutePrefixMiddleware implements MiddlewareInterface
{
    private $container;
    private $prefix;
    private $middleware;
    
    /**
     * __construct
     *
     * @param  mixed $container
     * @param  mixed $prefix
     * @param  mixed $middleware
     * @return void
     */
    public function __construct($container, $prefix, $middleware)
    {
        $this->container = $container;
        $this->prefix = $prefix;
        $this->middleware = $middleware;
    }
    
    /**
     * process
     *
     * @param  ServerRequestInterface $request
     * @param  RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path= $request->getUri()->getPath();
        if (strpos($path, $this->prefix)===0) {
            return $this->container->get($this->middleware)->process($request, $handler);
        }
        return $handler->handle($request);
    }
}
