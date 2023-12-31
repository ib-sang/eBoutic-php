<?php

namespace App\Services\Role\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Table\PersonnelTable;
use App\Services\Role\Entity\RoleEntity;
use App\Services\Role\Table\RoleTable;
use Controllers\Action\CrudAction;
use Controllers\Database\Hydrator;
use Controllers\Database\QueryResult;
use Controllers\Renderer\RendererInterface;
use Controllers\Validator;
use Psr\Http\Message\ServerRequestInterface;

class RoleCrudAction extends CrudAction
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
    protected $personnel;

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
        PersonnelTable $personnel,
        StatusTable $statusTable,
        DatabaseAuth $auth,
        RoleTable $table
    ) {
        parent::__construct($renderer, $auth, $table, $statusTable, $enterprise, $personnel);
    }
    
    public function create(ServerRequestInterface $request)
    {
        $items = $this->getNewEntity();
        $params = $this->getParams($request, $items);
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                // var_dump($params); die();
                $this->table->insert($params);
                $this->response['status'] = 201;
                $this->response['message'] = 'Bus Registration Successfull!';
                unset($params['password']);
                $this->response['data'] = [
                    'status' => $this->response['status'],
                    'message' => $this->response['message'],
                    'enterprises' => $params,
                    'request' => [
                        'type' => 'GET',
                        'url' => 'http:localhost:3000/api/v1/busies'
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
            $this->response['data']['status'] = $this->response['status'];
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
        $keysUser = ['firstname', 'lastname', 'email', 'phone', 'username', 'usersId'];
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
        $post = new RoleEntity();
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
                    'name',
                    'users_id',
                    'enterprises_id'
                    ]
            );
        }, ARRAY_FILTER_USE_KEY);
        $params['created_at'] = $post->created_at;
        // $params['device'] = json_encode($params['device']);
        
        return $params;
    }

    protected function getValidator($request): Validator
    {
        $validator = parent::getValidator($request)
            ->required('name', 'users_id', 'enterprises_id')
            ->notEmpty('name', 'users_id', 'enterprises_id');
        return $validator;
    }
}
