<?php

namespace Controllers;

use Mezzio\Router\Route as MezzioRoute;
use Controllers\Router\Route;
use Mezzio\Router\FastRouteRouter;
use Controllers\Auth\User;
use Psr\Http\Message\ServerRequestInterface;
use Controllers\Middleware\CallableMiddleware;

/**
 * Router
 * Register and match routes
 * @package Controller
 */
class Router
{

    private $router;

    public function __construct()
    {
        $this->router = new FastRouteRouter();
    }
    
    /**
     * get
     *
     * @param  string $path
     * @param  mixed $callable
     * @param  string/null $name
     * @return void
     */
    public function get(string $path, $callback, ?string $name = null):void
    {
        $this->router->addRoute(new MezzioRoute($path, new CallableMiddleware($callback), ['GET'], $name));
    }
    
    /**
     * post
     *
     * @param  string $path
     * @param  mixed $callable
     * @param  string/null $name
     * @return void
     */
    public function post(string $path, $callable, ?string $name = null):void
    {
        $this->router->addRoute(new MezzioRoute($path, new CallableMiddleware($callable), ['POST'], $name));
    }

    /**
     * post
     *
     * @param  string $path
     * @param  mixed $callable
     * @param  string/null $name
     * @return void
     */
    public function put(string $path, $callable, ?string $name = null):void
    {
        $this->router->addRoute(new MezzioRoute($path, new CallableMiddleware($callable), ['PUT'], $name));
    }
    
    /**
     * delete
     *
     * @param  string $path
     * @param  mixed $callable
     * @param  string/null $name
     * @return void
     */
    public function delete(string $path, $callable, ?string $name = null):void
    {
        $this->router->addRoute(new MezzioRoute($path, new CallableMiddleware($callable), ['delete'], $name));
    }
    
    /**
     * crud
     *
     * @param  string $prefixPath
     * @param  mixed $callable
     * @param  string $prefixName
     * @return void
     */
    public function crud(string $prefixPath, $callable, string $prefixName, ?User $user = null):void
    {
        $subpath='';
        $this->get($subpath."$prefixPath", $callable, "$prefixName.index");
        $this->get($subpath."$prefixPath/new", $callable, "$prefixName.create");
        $this->post($subpath."$prefixPath/new", $callable);
        $this->get($subpath."$prefixPath/edit/{id:\d+}", $callable, "$prefixName.edit");
        $this->post($subpath."$prefixPath/edit/{id:\d+}", $callable);
        $this->put($subpath."$prefixPath/edit/{id:\d+}", $callable);
        $this->get("$prefixPath/{id:\d+}", $callable, "$prefixName.show");
        $this->delete($subpath."$prefixPath/{id:\d+}", $callable, "$prefixName.delete");
    }
    
    /**
     * match
     *
     * @param  ServerRequestInterface $request
     * @return null/Route
     */
    public function match(ServerRequestInterface $request):?Route
    {
        $result = $this->router->match($request);
        if ($result->isSuccess()) {
            return new Route(
                $result->getMatchedRouteName(),
                $result->getMatchedRoute(),
                $result->getMatchedParams()
            );
        }
        return null;
    }
    
    /**
     * generateUri
     *
     * @param  string $name
     * @param  array $params
     * @param  array $queryParams
     * @return null/string
     */
    public function generateUri(string $name, array $params = [], array $queryParams = []):?string
    {
        $uri = $this->router->generateUri($name, $params);
        if (!empty($queryParams)) {
            return $uri.'?'.http_build_query($queryParams);
        }
        return $uri;
    }

    /**
     * crudApi
     *
     * @param  string $prefixPath
     * @param  mixed $callable
     * @param  string $prefixName
     * @return void
     */
    public function crudApi(string $prefixPath, $callable, string $prefixName):void
    {
        $this->get("$prefixPath", $callable, "$prefixName.index");
        $this->get("$prefixPath/new", $callable, "$prefixName.create");
        $this->post("$prefixPath/new", $callable);
        $this->get("$prefixPath/edit/{id:\d+}", $callable, "$prefixName.edit");
        $this->get("$prefixPath/{id:\d+}", $callable, "$prefixName.show");
        $this->post("$prefixPath/edit/{id:\d+}", $callable);
        $this->delete("$prefixPath/{id:\d+}", $callable, "$prefixName.delete");
    }
    
    /**
     * getAPI
     *
     * @param  string $prefixPath
     * @param  mixed $callable
     * @param  string $prefixName
     * @return void
     */
    public function getAPI(string $path, $callback, string $name)
    {
        $this->router->addRoute(new MezzioRoute($path, new CallableMiddleware($callback), ['GET'], $name));
    }
}
