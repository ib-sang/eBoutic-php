<?php

namespace App\Services\Users\Actions;

use App\Services\Auth\DatabaseAuth;
use Controllers\Validator;
use Controllers\Action\CrudAction;
use App\Services\Auth\Table\UserTable;
use App\Services\Auth\Entity\UserEntity;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Table\PersonnelTable;
use Controllers\Database\Hydrator;
use Controllers\Database\QueryResult;
use Controllers\Renderer\RendererInterface;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class CrudProfileAction extends CrudAction
{

    protected $renderer;
    protected $statusTable;
    protected $enterprise;
    protected $personnel;
    protected $table;
    protected $auth;

    public function __construct(RendererInterface $renderer, DatabaseAuth $auth, UserTable $table, StatusTable $statusTable,  EnterpriseTable $enterprise, PersonnelTable $personnel)
    {
        parent::__construct($renderer, $auth, $table, $statusTable, $enterprise, $personnel);
    }

    public function create(ServerRequestInterface $request)
    {
        $errors = '';
        $table = $this->table->getTable();
        $item = $this->getNewEntity();
        $user = $this->auth->getUser();

        $statusParams = [
            "message" => "La liste de données dans la base",
            "user" => $user,
            "table" => $table,
        ];
        $roleUser = json_decode($user->roles)->role;
        $userId = $user->id;
        if (array_search('role_users', $roleUser)!==false && !(array_search('role_admin', $roleUser)!==false)) {
            $personnel = $this->personnel->findBy('users_id', $userId);
            $enterprise = $this->getEnterprise($userId, $personnel->enterpriseId);
        }

        if (array_search('role_admin', $roleUser)!==false) {
            $enterprise = $this->getEnterprise($userId);
        }
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $params = $this->getParams($request, $item);
                $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
                if (!is_null($params)) {
                    $this->table->insert($params);
                    $newEntity = $this->table->findLatest();
                    $this->response['status'] = 201;
                    $this->response['data']['status'] = 201;
                    $this->response['message'] = $table.' Registration Successfull!';
                    $this->response['data']['message'] = $table.' Registration Successfull!';
                    $this->response['data'] = [
                        'message' => $this->response['message'],
                        $table => $newEntity,
                        'request' => [
                            'message' => 'ALL_ENTITY',
                            'type' => 'GET',
                            'url' => 'http:localhost:3000/api/v1/'.$table
                        ]
                    ];
                    $statusParams["data"] = $params;
                    $statusParams["response"] = $this->response;

                    $this->statusTable->insert([
                        "name" =>"created" .$table,
                        "description" => "un entré de données de la table: ".$table.", a été ajouté appélé par " .$user->username,
                        "status" => json_encode($statusParams),
                        "enterprise_id" => $enterprise->id,
                        "created_at" => new DateTime()
                    ]);
                    return $this->renderer->renderapi(
                        $this->response["status"],
                        $this->response['data'],
                        $this->response['message']
                    );
                }
                $this->response['status'] = 404;
                $this->response['message'] = "all fields dons't valided";
                $this->response['data']['message'] = $this->response['message'];
                $this->response["data"]['errors'] = ['errors' =>'error inconnu'];
                $this->response["data"]["request"] = [
                    'message' => 'CREATE_ENTITY',
                    'type' => 'POST',
                    'url' => 'http://localhost:3000/'.$table.'/new',
                    'data' => ['form data']
                ];
                
                $statusParams["response"] = $this->response;

                $this->statusTable->insert([
                    "name" =>"errorcreated" .$table,
                    "description" => "Il y'a eu une erreur de validation des champs d'entré de données de la table: ".$table.", faite par " .$user->username,
                    "status" => json_encode($statusParams),
                    "enterprise_id" => $enterprise->id,
                    "created_at" => new DateTime()
                ]);

                return $this->renderer->renderapi(
                    $this->response["status"],
                    $this->response['data'],
                    $this->response['message']
                );
            }
            
            $errors = $validator->getErrors();
            Hydrator::hydrate($request->getParsedBody(), $item);
            $this->response['status'] = 404;
            $this->response['message'] = "all fields dons't valided";
            $this->response['data']['message'] = $this->response['message'];
            $this->response["data"]['errors'] = $this->getErrorValidator($errors);
            $this->response["data"]["request"] = [
                'message' => 'CREATE_ENTITY',
                'type' => 'POST',
                'url' => 'http://localhost:3000/'.$table.'/new',
                'data' => ['form data']
            ];

            $statusParams["data"] = $errors;
            $statusParams["response"] = $this->response;

            $this->statusTable->insert([
                "name" =>"errorcreated" .$table,
                "description" => "Il y'a eu une erreur de validation des champs d'entré de données de la table: ".$table.", faite par " .$user->username,
                "status" => json_encode($statusParams),
                "enterprise_id" => $enterprise->id,
                "created_at" => new DateTime()
            ]);
            
            return $this->renderer->renderapi(
                $this->response["status"],
                $this->response['data'],
                $this->response['message']
            );
        }
        $this->response['status'] = 500;
        $this->response['data']['message'] = $this->response['message'];
        $this->response["data"]["request"] = [
            'message' => 'CREATE_ENTERPRISES',
            'type' => 'POST',
            'url' => 'http://localhost:3000/'.$table.'/new',
            'data' => ['form data']
        ];

        $statusParams["response"] = $this->response;

        $this->statusTable->insert([
            "name" =>"errorcreated" .$table,
            "description" => "Il y'a eu une erreur de la table: ".$table.", appélé par " .$user->username,
            "status" => json_encode($statusParams),
            "enterprise_id" => $enterprise->id,
            "created_at" => new DateTime()
        ]);

        return $this->renderer->renderapi(
            $this->response["status"],
            $this->response['data'],
            $this->response['message']
        );
    }

    public function edit(ServerRequestInterface $request)
    {
        $errors = '';
        $table = $this->table->getTable();
        $id = $request->getAttribute('id');
        $item = $this->getNewEntity();
        $user = $this->auth->getUser();

        $statusParams = [
            "message" => "La liste de données dans la base",
            "user" => $user,
            "table" => $table,
        ];
        // $roleUser = json_decode($user->roles)->role;
        // $userId = $user->id;
        // if (array_search('role_users', $roleUser)!==false && !(array_search('role_admin', $roleUser)!==false)) {
        //     $personnel = $this->personnel->findBy('users_id', $userId);
        //     $enterprise = $this->getEnterprise($userId, $personnel->enterpriseId);
        // }

        // if (array_search('role_admin', $roleUser)!==false) {
        //     $enterprise = $this->getEnterprise($userId);
        // }

        if ($request->getMethod() === 'POST' || $request->getMethod() === 'PUT') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $params = $this->getParams($request, $item);
                $pass = $user->password;
                if (array_key_exists('password', $params) && !password_verify($params['password'], $pass) && password_verify($params['lastPass'], $pass)) {
                    $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
                    unset($params['lastPass']);
                    // var_dump($params); die();
                    $this->table->update($id, $params);
                    $this->response['status'] = 201;
                    $this->response['message'] = $table.' Updated Successfull!';

                    $newEntity = $this->table->findLatest();
                    $this->response['data'] = [
                        'status' => $this->response['status'],
                        'message' => $this->response['message'],
                        $table => $newEntity,
                        'request' => [
                            'message' => 'ALL_ENTITY',
                            'type' => 'GET',
                            'url' => 'http:localhost:3000/api/v1/'.$table
                        ]
                    ];
                    
                    $statusParams["data"] = [
                        "old" => $this->table->find($id),
                        "new" => $newEntity
                    ];
                    $statusParams["response"] = $this->response;
    
                    $this->statusTable->insert([
                        "name" =>"edit" .$table,
                        "description" => "Il y'a eu un changement d'entré de données de la table: ".$table.", faite par ",
                        "status" => json_encode($statusParams),
                        // "enterprise_id" => $enterprise->id,
                        "created_at" => new DateTime()
                    ]);
                    return $this->renderer->renderapi(
                        $this->response["status"],
                        $this->response['data'],
                        $this->response['message']
                    );
                }
                $this->response['status'] = 404;
                $this->response['data']['status'] = 404;
                $this->response['message'] = "password fields dons't valided";
                $this->response['data']['message'] = $this->response['message'];
                $this->response["data"]["request"] = [
                    'message' => 'CREATE_ENTITY',
                    'type' => 'POST',
                    'url' => 'http://localhost:3000/'.$table.'/new',
                    'data' => ['fom data']
                ];

                $statusParams["data"] = $errors;
                $statusParams["response"] = $this->response;

                $this->statusTable->insert([
                    "name" =>"erroredit" .$table,
                    "description" => "Il y'a eu une erreur de validation des champs d'entré de données de la table: ".$table.", faite par ",
                    "status" => json_encode($statusParams),
                    // "enterprise_id" => $enterprise->id,
                    "created_at" => new DateTime()
                ]);

                return $this->renderer->renderapi(
                    $this->response["status"],
                    $this->response['data'],
                    $this->response['message']
                );
            }
            
            $errors = $validator->getErrors();
            Hydrator::hydrate($request->getParsedBody(), $item);
            $this->response['status'] = 404;
            $this->response['data']['status'] = 404;
            $this->response['message'] = "all fields dons't valided";
            $this->response['data']['message'] = $this->response['message'];
            $this->response["data"]['errors'] = $this->getErrorValidator($errors);
            $this->response["data"]["request"] = [
                'message' => 'CREATE_ENTITY',
                'type' => 'POST',
                'url' => 'http://localhost:3000/'.$table.'/new',
                'data' => ['fom data']
            ];

            $statusParams["data"] = $errors;
            $statusParams["response"] = $this->response;

            $this->statusTable->insert([
                "name" =>"erroredit" .$table,
                "description" => "Il y'a eu une erreur de validation des champs d'entré de données de la table: ".$table.", faite par ",
                "status" => json_encode($statusParams),
                // "enterprise_id" => $enterprise->id,
                "created_at" => new DateTime()
            ]);
            
            return $this->renderer->renderapi(
                $this->response["status"],
                $this->response['data'],
                $this->response['message']
            );
        }
        $this->response['status'] = 500;
        $this->response['data']['status'] = 500;
        $this->response['data']['message'] = $this->response['message'];
        $this->response["data"]["request"] = [
            'message' => 'CREATE_ENTITY',
            'type' => 'POST',
            'url' => 'http://localhost:3000/'.$table.'/new',
            'data' => ['form data']
        ];

        $statusParams["response"] = $this->response;

        $this->statusTable->insert([
            "name" =>"erroredit" .$table,
            "description" => "Il y'a eu une erreur de la base pour la table: ".$table.", faite par ",
            "status" => json_encode($statusParams),
            // "enterprise_id" => $enterprise->id,
            "created_at" => new DateTime()
        ]);

        return $this->renderer->renderapi(
            $this->response["status"],
            $this->response['data'],
            $this->response['message']
        );
    }

    protected function getNewEntity()
    {
        $post = new UserEntity();
        $post->created_at = new \DateTime();
        return $post;
    }

    protected function getParamForm($entity)
    {
        $tab = [];
        $tabUser = [];
        if ($entity instanceof QueryResult) {
            $entity = $entity->getRecords();
        }
        foreach ($entity as $k => $v) {
            if (!is_null($v) && $k !== "usersId" && $k !== "password") {
                $tab[$k] = $v;
            }
            // $tab[$k]['user'] = $tabUser;
        }
        
        return $tab;
    }

    protected function getParams(ServerRequestInterface $request, $post)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $params = array_merge(
            $data,
            $request->getUploadedFiles(),
            ["created_at" => $post->created_at]
        );
        
        
        if (array_key_exists('roles', $params)) {
            $params['roles'] = json_encode([$params['role'] => 'role_'.$params['role']]) ;
        }
        return array_filter($params, function ($keys) {
            return  in_array(
                $keys,
                [
                    'username',
                    'password',
                    'phone',
                    'email',
                    'created_at',
                    'firstname',
                    'lastname',
                    'roles',
                    'sexe',
                    'description',
                    'adress',
                    'lastPass'
                    ]
            );
        }, ARRAY_FILTER_USE_KEY);
        $params['created_at'] =$post->created_at;
        return $params;
    }
}
