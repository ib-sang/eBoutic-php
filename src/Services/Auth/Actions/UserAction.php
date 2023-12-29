<?php

namespace App\Services\Auth\Actions;

use App\Services\Auth\DatabaseAuth;
use Controllers\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserAction
{

    private $renderer;
    private $auth;

    private $response = [
        "status" => 500,
        "message" => "erreur de connexion à la base de donnée",
        "data" => null
    ];

    public function __construct(DatabaseAuth $auth, RendererInterface $renderer)
    {
        $this->renderer = $renderer;
        $this->auth = $auth;
    }
    
    public function __invoke(ServerRequestInterface $request)
    {
        $user = $this->auth->isValid(['Authorization'=>$request->getHeader('Authorization')]);
        if ($user['user']) {
            $path = $this->getParamForm($user['user']);
            $this->response['status'] = 201;
            $this->response['data']['message'] = 'Auth successful';
            $this->response['data']['user'] = $path;
            $this->response['data']['request'] = [
                'type' => 'GET',
                'url' => 'http:localhost:3000/api/v1/?',
            ];
            return $this->renderer->renderapi(
                $this->response['status'],
                $this->response['data'],
                $this->response['message']
            );
        }
        $this->response['data']['auth'] = $user;
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

    public function getParamForm($entity)
    {
        $tab = [];
        foreach ($entity as $key => $value) {
            if (!is_null($value) && $key!=='usersId'&& $key!=='password') {
                $tab[$key] = $value;
            }
        }
        return $tab;
    }
}
