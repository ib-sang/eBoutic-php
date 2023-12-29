<?php

namespace Controllers\Renderer;

use Psr\Http\Message\ResponseInterface;

interface RendererInterface
{

    
    /**
     * Added the path for load the views
     *
     * addPath
     *
     * @param  string $namespace
     * @param  null/string $path
     * @return void
     */
    public function addPath(string $namespace, ?string $path):void;
        
    /**
     * Retruned one view
     *
     * render
     *
     * @param  string $view
     * @param  array $params
     * @return string
     */
    public function render(string $view, array $params = []):string;
    
    /**
     * addGlobal
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function addGlobal(string $key, $value):void;

    public function renderapi(string $status, $body, string $reason = null):string;
}
