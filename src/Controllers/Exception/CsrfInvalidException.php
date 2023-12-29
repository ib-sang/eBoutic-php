<?php

namespace Controllers\Exception;

use Controllers\Router;
use Controllers\Renderer\RendererInterface;

class CsrfInvalidException extends \Exception
{
    public function __construct(
        RendererInterface $renderer,
        Router $router
    ) {
        $renderer->addPath('nofound', __DIR__.'/views');
        $router->get('/nofoundresponse', NoFoundAction::class, 'nofound');
    }
}
