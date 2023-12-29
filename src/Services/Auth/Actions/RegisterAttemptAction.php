<?php

namespace App\Services\Auth\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Auth\Entity\UserEntity;
use App\Services\Auth\Table\UserTable;
use Controllers\Database\Hydrator;
use Controllers\Renderer\RendererInterface;
use Controllers\Validator;
use Psr\Http\Message\ServerRequestInterface;

class RegisterAttemptAction
{

    private $auth;
    private $renderer;
    private $table;
    private $router;
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
        DatabaseAuth $auth,
        UserTable $table
    ) {
        $this->auth = $auth;
        $this->table = $table;
        $this->renderer = $renderer;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        
        $items = $this->getNewEntity();
        $params = $this->getParams($request, $items);
        $password = $params['password'];
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $params['password'] = password_hash($password, PASSWORD_DEFAULT);
                $params['roles'] = json_encode(['role' => ['role_admin']]);
                // var_dump($params); die();
                $this->table->insert($params);
                $this->response['status'] = 201;
                $this->response['message'] = 'User Registration Successfull!';
                unset($params['password']);
                // $role = json_decode($params['roles'])->role;
                // $params['roles'] = explode('_', $role)[1];
                $this->response['data'] = [
                    'message' => $this->response['message'],
                    'user' => $params,
                    'request' => [
                        'type' => 'POST',
                        'url' => 'http:localhost:3000/api/v1/login',
                        'data' => ['username', 'password']
                    ]
                ];
                
                return $this->renderer->renderapi(
                    $this->response["status"],
                    $this->response['data'],
                    $this->response['message']
                );
            }
            $errors = $validator->getErrors();
            Hydrator::hydrate($request->getParsedBody(), $items);
            $this->response['data']['message'] = $this->response['message'];
            $this->response["data"]['errors'] = $this->getErrorValidator($errors);
            
            return $this->renderer->renderapi(
                $this->response["status"],
                $this->response['data'],
                $this->response['message']
            );
        }
    }

    protected function getNewEntity()
    {
        $post = new UserEntity();
        $post->created_at = new \DateTime();
        return $post;
    }

    protected function getParams(ServerRequestInterface $request, $post)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $params = array_merge(
            $data,
            $request->getParsedBody(),
            $request->getUploadedFiles(),
            ["created_at" => $post->created_at]
        );
       
        $params = array_filter($params, function ($keys) {
            return in_array(
                $keys,
                [
                    'username',
                    'password',
                    'phone',
                    'email',
                    'created_at',
                    'firstname',
                    'lastname',
                    'sexe',
                    'adress'
                    ]
            );
        }, ARRAY_FILTER_USE_KEY);
        
        return $params;
    }

    protected function getErrorValidator(array $errors):array
    {
        $errorParams =[];
        foreach ($errors as $key => $value) {
            $errorParams[$key] = $errors[$key]->__toString();
        };
        return $errorParams;
    }

    protected function getValidator($request): Validator
    {
        $validator = new Validator(array_merge($this->getParseBodyJSON($request), $request->getUploadedFiles()));
        $validator->required('username', 'password', 'passwordConfirmed', 'email', 'phone')
            ->notEmpty('username', 'password', 'passwordConfirmed', 'email', 'phone');
        return $validator;
    }

    protected function getParseBodyJSON(ServerRequestInterface $request):array
    {
        $tab = [];
        $array = json_decode($request->getBody()->getContents());
        foreach ($array as $k => $v) {
            $tab[$k] = $v;
        }
        return $tab;
    }
}
