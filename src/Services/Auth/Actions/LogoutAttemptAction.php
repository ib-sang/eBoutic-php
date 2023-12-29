<?php

namespace App\Services\Auth\Actions;

use App\Services\Auth\DatabaseAuth;
use Controllers\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogoutAttemptAction
{

    private $renderer;

    private $auth;

    private $response = [
        "status" => 500,
        "message" => "erreur de connexion à la base de donnée",
        "data" => null
    ];

    /**
     * __construct
     *
     * @param  RendererInterface $renderer
     * @param  DatabaseAuth $auth
     *
     * @return void
     */
    public function __construct(
        RendererInterface $renderer,
        DatabaseAuth $auth
    ) {
        $this->renderer = $renderer;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request)
    {

        $id = $request->getAttribute('id');

        if ($id) {
            $this->auth->signOut($id);
            $this->response['status'] = 201;
            $this->response['data']['message'] = 'sign out successful';
            $this->response['data']['request'] = [
                'type' => 'GET',
                'url' => 'http:localhost:3000/api/v1/login',
            ];
            return $this->renderer->renderapi(
                $this->response['status'],
                $this->response['data'],
                $this->response['message']
            );
        } else {
            $this->response['data']['message'] = 'indentifield failed.';
            $this->response['status'] = 404;
            $this->response['data']['status'] = 404;
            $this->response['data']['request'] = [
                'type' => 'POST',
                'url' => 'http:localhost:3000/api/v1/login',
                'data' => ['username', 'password']
            ];
            return $this->renderer->renderapi(
                $this->response['status'],
                $this->response['data'],
                $this->response['message']
            );
        }
        return $this->renderer->renderapi(
            $this->response['status'],
            $this->response['data'],
            $this->response['message']
        );
    }
}
