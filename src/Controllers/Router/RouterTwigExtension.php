<?php

namespace Controllers\Router;

use Controllers\Router;


use Twig\TwigFunction;
use Controllers\AbstractTwigExtension;

class RouterTwigExtension extends AbstractTwigExtension
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router=$router;
    }

    public function getFunctions():array
    {
        return [
            new TwigFunction('path', [$this,'pathFor']),
            new TwigFunction('is_subpath', [$this,'isSubpath'])
        ];
    }

    /**
     * generer l'url
     *
     * pathFor
     *
     * @param  mixed $path
     * @param  mixed $params
     *
     * @return string
     */
    public function pathFor(string $path, ?array $params = [])
    {
        return $this->router->generateUri($path, $params);
    }

    /**
     * verifier l'url
     *
     * isSubpath
     *
     * @param  mixed $path
     *
     * @return bool
     */
    public function isSubpath(string $path):bool
    {
        $uri=$_SERVER['REQUEST_URI'] ?? '/';
        $expectedUri=$this->router->generateUri($path)?? '/';
        return strpos($uri, $expectedUri)!==false;
    }
}
