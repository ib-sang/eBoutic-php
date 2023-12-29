<?php

namespace Controllers;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Controllers\Middleware\RoutePrefixMiddleware;

class App implements RequestHandlerInterface
{

    /**
     * index
     *
     * @var int
     */
    private int $index=0;
    /**
     * middleware
     *
     * @var array
     */
    private $middleware=[];
    /**
     * modules
     *
     * @var array
     */
    private $modules=[];
                
    /**
     * container
     *
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * definition
     *
     * @var string
     */
    private $definition;
    
    /**
     * __construct
     *
     * @param  string $definition
     * @return void
     */
    public function __construct(string $definition)
    {
        $this->definition=$definition;
    }

    /**
     * addModule
     *
     * @param  string $modules
     * @return self
     */
    public function addModule(string $modules):self
    {
        $this->modules[]=$modules;
        return $this;
    }
    
    /**
     * pipe
     *
     * @param  string $routePrefix
     * @param  string/null $middleware
     * @return self
     */
    public function pipe(string $routePrefix, ?string $middleware = null):self
    {
        if ($middleware===null) {
            $this->middleware[]=$routePrefix;
        } else {
            $this->middleware[]=new RoutePrefixMiddleware($this->getContainer(), $routePrefix, $middleware);
        }
        return $this;
    }
    
    /**
     * handle
     *
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request):ResponseInterface
    {
        $middleware=$this->getMiddleware();
        if (is_null($middleware)) {
            throw new \Exception("Aucun middleware n'a intercepter cette requête");
        } elseif (is_callable($middleware)) {
            return call_user_func_array($middleware, [$request,[$this,'process']]);
        } elseif ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }
    }
    
    /**
     * process
     *
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request):ResponseInterface
    {
        $middleware=$this->getMiddleware();
        if (is_null($middleware)) {
            throw new \Exception("Aucun middleware n'a intercepter cette requête");
        } elseif (is_callable($middleware)) {
            return call_user_func_array($middleware, [$request,[$this,'process']]);
        } elseif ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }
    }
        
    /**
     * run
     *
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function run(ServerRequestInterface $request):ResponseInterface
    {
        foreach ($this->modules as $module) {
            $this->getContainer()->get($module);
        }
        return $this->handle($request);
    }
    
    /**
     * getContainer
     *
     * @return ContainerInterface
     */
    public function getContainer():ContainerInterface
    {
        if ($this->container===null) {
            $builder=new ContainerBuilder();
            $builder->addDefinitions($this->definition);
            foreach ($this->modules as $module) {
                if ($module::DEFINITIONS) {
                    $builder->addDefinitions($module::DEFINITIONS);
                }
            }
            $this->container=$builder->build();
        }
        
        return $this->container;
    }
        
    /**
     * getModules
     *
     * @return void
     */
    public function getModules()
    {
        return $this->modules;
    }
    
    /**
     * getMiddleware
     *
     * @return MiddlewareInterface/null
     */
    private function getMiddleware():?MiddlewareInterface
    {
        if (array_key_exists($this->index, $this->middleware)) {
            if (is_string($this->middleware[$this->index])) {
                $middleware= $this->container->get($this->middleware[$this->index]);
            } else {
                $middleware=$this->middleware[$this->index];
            }
            $this->index++;
            return $middleware;
        }
        return null;
    }
}
