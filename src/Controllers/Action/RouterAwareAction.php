<?php

namespace Controllers\Action;

use GuzzleHttp\Psr7\Response;

/**
 * ajoute des méthode liées à l'utilisation
 *
 * trait RouterAwareAction
 *
 * @package Controller\Action
 */
trait RouterAwareAction
{

    /**
     * redirect
     *
     * @param string $path
     * @param array $params
     */
    public function redirect(string $path, array $params = [])
    {
        $redictedUri = $this->router->generateUri($path, $params);
        return (new Response())
            ->withStatus(301)
            ->withHeader('location', $redictedUri);
    }
}
