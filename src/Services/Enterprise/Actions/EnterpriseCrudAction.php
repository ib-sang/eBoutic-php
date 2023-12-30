<?php

namespace App\Services\Enterprise\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Enterprise\Entity\EnterpriseEntity;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Table\PersonnelTable;
use Controllers\Action\CrudAction;
use Controllers\Database\Hydrator;
use Controllers\Database\QueryResult;
use Controllers\Renderer\RendererInterface;
use Controllers\Validator;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class EnterpriseCrudAction extends CrudAction
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
        PersonnelTable $personnel,
        StatusTable $statusTable,
        DatabaseAuth $auth,
        EnterpriseTable $table
    ) {
        parent::__construct($renderer, $auth, $table, $statusTable, $table, $personnel);
    }

    public function show(ServerRequestInterface $request)
    {
        $id = $request->getAttribute('id');
        if ($id) {
            $data = $this->table->findByUser('users_id', '2', $id);
            $table = $this->table->getTable();
            // var_dump($data); die();
            if ($data !==false) {
                $this->response['status'] = 201;
                $this->response['data'][$table] = $this->getParamForm($data);
                $this->response['data']['message'] = "On item on database.";
                $this->response['message'] = "On item on database.";
                $this->response['data']['status'] = 201;
                return $this->renderer->renderapi(
                    $this->response['status'],
                    $this->response['data'],
                    $this->response['message']
                );
            }
            $this->response['data']['message'] = "No item on database.";
            $this->response['message'] = "No item on database.";
            $this->response['data']['status'] = 404;
            return $this->renderer->renderapi(
                $this->response['status'],
                $this->response['data'],
                $this->response['message']
            );
        }

        $this->response['data'];
        $this->response['data']['message'] = "No entries fount.";
        $this->response['message'] = "No entries fount.";
        return $this->renderer->renderapi(
            $this->response['status'],
            $this->response['data'],
            $this->response['message']
        );
    }

    public function index(ServerRequestInterface $request)
    {
        $id = $this->auth->getUser()->id;
        $user = $this->auth->getUser();
        $params = $request->getQueryParams();
        $table = $this->table->getTable();
        // $statusParams = [
        //     "message" => "La liste de données dans la base",
        //     "user" => $user,
        //     "table" => $table,
        // ];
        $currentPage = $params['p'] ?? 1;
        $items = $this->table->findAllPublic()->paginate(12, $currentPage);
        $count = $items->getNbResults();
        $nbPage = $items->getNbPages();
        if ($count !== 0) {
            $tab = $this->getParamForm($items->getIterator());
            $this->response['status'] = 201;
            $this->response['data']['message'] = 'All '.$table;
            $this->response['message'] = 'All '.$table;
            $this->response['data']['currentPage'] = $currentPage;
            $this->response['data']['nbPage'] = $nbPage;
            $this->response['data']['count'] = $count;
            $this->response['data'][$table] = $tab;
            $this->response['data']['request'] = [
                'message' => 'CREATED_ENTITY',
                'type' => 'POST',
                'url' => 'http://localhost:3000/api/v1/'.$table.'/new',
                'data' => ['form data']
            ];

            // $statusParams["data"] = $tab;
            // $statusParams["response"] = $this->response;

            // $this->statusTable->insert([
            //     "name" =>"getall" .$table,
            //     "description" => "Liste de données de la table: ".$table." dans la base, appélé par " .$user->username,
            //     "status" => json_encode($statusParams),
            //     "enterprise_id" => $user->enterprise->id,
            //     "created_at" => new DateTime()
            // ]);

            return $this->renderer->renderapi(
                $this->response['status'],
                $this->response['data'],
                $this->response['message']
            );
        }
        $this->response['data'];
        $this->response['data']['message'] = "No entries fount.";
        $this->response['message'] = "No entries fount.";

        // $statusParams["response"] = $this->response;
        // $this->statusTable->insert([
        //     "name" =>"errorsforget" .$table,
        //     "description" => "Ils n'y a aucun donnée dans la base",
        //     "status" => json_encode($statusParams),
        //     "enterprise_id" => $user->enterprise->id,
        //     "created_at" => new DateTime()
        // ]);
        return $this->renderer->renderapi(
            $this->response['status'],
            $this->response['data'],
            $this->response['message']
        );
    }
    
    public function create(ServerRequestInterface $request)
    {
        $items = $this->getNewEntity();
        $params = $this->getParams($request, $items);
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $this->table->insert($params);
                $this->response['status'] = 201;
                $this->response['message'] = 'Enterprise Registration Successfull!';
                unset($params['password']);
                $this->response['data'] = [
                    'message' => $this->response['message'],
                    'enterprises' => $params,
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

    protected function getParamForm($entity)
    {
        $tab = [];
        $tabUser = [];
        $keysUser = ['', 'email', 'firstname', 'lastname', 'username', 'usersId'];
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
                $response['data'][$k] = $tab;
            }
        } else {
            foreach ($entity as $key => $value) {
                if (!is_null($value) && $key !== "password") {
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
        $post = new EnterpriseEntity();
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
                    'city',
                    'phone',
                    'towers',
                    'users_id',
                    'image',
                    'enterprise_capi',
                    'enterprise_com',
                    'adress'
                    ]
            );
        }, ARRAY_FILTER_USE_KEY);
        $params['created_at'] = $post->created_at;
        return $params;
    }

    protected function getValidator($request): Validator
    {
        $validator = parent::getValidator($request)
            ->required('name', 'phone', 'city', 'towers', 'users_id', 'pays')
            ->notEmpty('name', 'phone', 'city', 'towers', 'users_id', 'pays');
        return $validator;
    }

}
