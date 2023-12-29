<?php

namespace Controllers\Middleware;

use Controllers\Router\Route;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatcherMiddleware implements MiddlewareInterface
{

    /**
     * container
     *
     * @var ContainerInterface
     */
    private $container;
        
    /**
     * __construct
     *
     * @param  ContainerInterface $container
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container=$container;
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
        $route = $request->getAttribute(Route::class);
        
        if (is_null($route)) {
            return $handler->handle($request);
        }
        
        $middleware = $route->getCallback()->getMiddleware();
        $callback=$middleware->getCallback();
        if (is_string($callback)) {
            $callback=$this->container->get($callback);
        }
        
        $response=call_user_func_array($callback, [$request]);
        
        if ($this->isJson($response)) {
            header("Content-type:application/json");
            $tab = json_decode($response);
            if (is_array($tab)) {
                return new Response(
                    $tab["status"] ?? 200,
                    [
                        'Content-Type: application/json',
                    ],
                    json_encode($tab["body"]) ?? $response
                );
            }
            return new Response(
                $tab->status ?? 200,
                ['Content-Type: application/json'],
                json_encode($tab->body) ?? $response
            );
        }
        if (is_string($response)) {
            return new Response(200, [], $response);
        } elseif ($response instanceof ResponseInterface) {
            return $response;
        } else {
            throw new \Exception('The response is not a string or an instance of ResponseIterface ');
        }
    }

    private function isJson($string)
    {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
    }
}
