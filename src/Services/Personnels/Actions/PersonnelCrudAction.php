<?php

namespace App\Services\Personnels\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Auth\Table\UserTable;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Entity\PersonnelEntity;
use App\Services\Personnels\Table\PersonnelTable;
use Controllers\Action\CrudAction;
use Controllers\Database\Hydrator;
use Controllers\Database\QueryResult;
use Controllers\Renderer\RendererInterface;
use Controllers\Validator;
use Psr\Http\Message\ServerRequestInterface;

class PersonnelCrudAction extends CrudAction
{
    protected $renderer;
    protected $table;
    protected $router;
    protected $response = [
        "status" => 500,
        "message" => "erreur de connexion à la base de donnée",
        "data" => null
    ];

    protected $auth;
    protected $statusTable;
    protected $enterprise;
    private $tableUser;

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
        EnterpriseTable $enterprise,
        StatusTable $statusTable,
        DatabaseAuth $auth,
        PersonnelTable $table,
        UserTable $tableUser
    ) {
        parent::__construct($renderer, $auth, $table, $statusTable, $enterprise, $table);
        $this->tableUser = $tableUser;
        $this->auth = $auth;
    }
    
    public function create(ServerRequestInterface $request)
    {
        $items = $this->getNewEntity();
        $params = $this->getParams($request, $items);
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                // create a user add to personnal
                $paramsUser = array_filter($params, function ($keys) {
                    return in_array(
                        $keys,
                        [
                            'firstname',
                            'lastname',
                            'phone',
                            'password',
                            'username',
                            'role'
                        ]
                    );
                }, ARRAY_FILTER_USE_KEY);

                $paramsUser['roles'] = json_encode(['role' => ['role_personnel', 'role_users', 'role_'.$paramsUser['role']]]);
                unset($paramsUser['role']);
                // var_dump($paramsUser); die();
                $this->tableUser->insert($paramsUser);

                // create personnal
                $paramsPerson = array_filter($params, function ($keys) {
                    return in_array(
                        $keys,
                        [
                            'salaire',
                            'enterprise_id',
                            'boutics_id',
                            'users_add'
                        ]
                    );
                }, ARRAY_FILTER_USE_KEY);
                // var_dump($paramsPerson); die();
                $paramsPerson['users_id'] = $this->tableUser->getPdo()->lastInsertId();
                // $paramsPerson['users_id'] = 5;
                $this->table->insert($paramsPerson);

                $this->response['status'] = 201;
                $this->response['message'] = 'Personnal Registration Successfull!';
                unset($params['password']);
                $this->response['data'] = [
                    'message' => $this->response['message'],
                    'enterprises' => $params,
                    'request' => [
                        'type' => 'GET',
                        'url' => 'http:localhost:3000/api/v1/reservations'
                        // 'data' => ['username', 'password']
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

    protected function getParamForm($entity)
    {
        $tab = [];
        $tabUser = [];
        $keysUser = ['ttf','firstname', 'lastname', 'email', 'phone', 'username', 'usersId'];
        if ($entity instanceof QueryResult) {
            foreach ($entity->getRecords() as $k => $v) {
                foreach ($v as $key => $value) {
                    if (!is_null($value)) {
                        if (array_search($key, $keysUser)) {
                            $tabUser[$key] = $value;
                        } else {
                            $tab[$key] = $value;
                        }
                    }
                }
                $tab["user"] = $tabUser;
                // $tab[$k] = $tab;
                $response['data'][$k] = $tab;
            }
        } else {
            foreach ($entity as $key => $value) {
                if (!is_null($value) && $key !== "password") {
                    // var_dump(array_search($key, $keysUser)); die();
                    if (array_search($key, $keysUser)) {
                        $tabUser[$key] = $value;
                    } else {
                        $tab[$key] = $value;
                    }
                }
            }
            $tab;
            $tab["user"] = $tabUser;
            $response['data'] = $tab;
        }
        
        
        return empty($tab) ? null : $response;
    }


    protected function getNewEntity()
    {
        $post = new PersonnelEntity();
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
                    'users_add',
                    'enterprises_id',
                    'firstname',
                    'lastname',
                    'phone',
                    'boutics_id',
                    'salaire',
                    'role'
                ]
            );
        }, ARRAY_FILTER_USE_KEY);
        if (!array_key_exists('password', $params)) {
            $params['password'] = "123";
        }
        $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        $params['created_at'] = $post->created_at;
        return $params;
    }

    protected function getValidator($request): Validator
    {
        $validator = parent::getValidator($request)
            ->required(
                'username',
                'users_add',
                'enterprises_id',
                'firstname',
                'lastname',
                'phone',
                'salaire',
                'role'
            )
            ->notEmpty(
                'username',
                'users_add',
                'enterprises_id',
                'firstname',
                'lastname',
                'phone',
                'salaire',
                'role'
            );
        return $validator;
    }
}
