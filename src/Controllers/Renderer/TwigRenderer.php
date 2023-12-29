<?php

namespace Controllers\Renderer;

use Twig\Environment;

class TwigRenderer implements RendererInterface
{

    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Added the path for load the views
     *
     * addPath
     *
     * @param  string $namespace
     * @param  null/string $path
     * @return void
     */
    public function addPath(string $namespace, ?string $path):void
    {
        $this->twig->getLoader()->addPath($path, $namespace);
    }

    public function renderapi(string $status, $body, ?string $reason = null):string
    {
        $contents = [
            'headers' => ['Content-Type: application/json'],
            'status' => $status,
            "body" => $body
        ];
        return json_encode($contents);
    }

        
    /**
     * Retruned one view
     *
     * render
     *
     * @param  string $view
     * @param  array $params
     * @return string
     */
    public function render(string $view, array $params = []):string
    {
        return $this->twig->render($view.'.html.twig', $params);
    }
    
    /**
     * addGlobal
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function addGlobal(string $key, $value):void
    {
        $this->twig->addGlobal($key, $value);
    }


    /**
     * Get the value of twig
     */
    public function getTwig()
    {
        return $this->twig;
    }
}
