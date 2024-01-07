<?php 


namespace App\Services\Product\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Table\PersonnelTable;
use App\Services\Product\Entity\StockEntity;
use App\Services\Product\Table\StockTable;
use Controllers\Action\CrudAction;
use Controllers\Database\Hydrator;
use Controllers\Database\QueryResult;
use Controllers\Renderer\RendererInterface;
use Controllers\Validator;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class StockCrudAction extends CrudAction
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
        DatabaseAuth $auth,
        StockTable $table,
        StatusTable $statusTable
    ) {
        parent::__construct($renderer, $auth, $table, $statusTable, $enterprise, $personnel);
    }

    public function index(ServerRequestInterface $request)
    {
        $user = $this->auth->getUser();
        $params = $request->getQueryParams();
        $table = $this->table->getTable();

        if ($this->table->getTable() == 'users') {
            $items = $this->table->findAllPublicBy('id', '!2')->paginate(70, $params['p'] ?? 1);
        }
        $currentPage = $params['p'] ?? 1;
        $items = $this->table->findAllPublic()->paginate(70, $currentPage);
        $count = $items->getNbResults();
        $nbPage = $items->getNbPages();
        if ($count !== 0) {

            $tab = $this->getParamForm($items->getIterator());
            $this->response['status'] = 201;
            $this->response['data']['status'] = 201;
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
            //     "enterprise_id" => $enterprise->id,
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
        $this->response['data']['status'] = 201;
        $this->response['message'] = "No entries fount.";

        // $statusParams["response"] = $this->response;
        // $this->statusTable->insert([
        //     "name" =>"errorgetall" .$table,
        //     "description" => "Ils n'y a aucun donnée dans la base",
        //     "status" => json_encode($statusParams),
        //     "enterprise_id" => $enterprise->id,
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
        $table = $this->table->getTable();
        $user = $this->auth->getUser();

        $statusParams = [
            "message" => "La liste de données dans la base",
            "user" => $user,
            "table" => $table,
        ];
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                // var_dump($params); die();
                $this->table->insert($params);
                $this->response['status'] = 201;
                $this->response['message'] = 'Agence Registration Successfull!';
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
                
                $statusParams["data"] = $params;
                $statusParams["response"] = $this->response;

                $this->statusTable->insert([
                    "name" =>"created" .$table,
                    "description" => "un entré de données de la table: ".$table.", a été ajouté appélé par " ,
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
            Hydrator::hydrate($request->getParsedBody(), $items);
            $this->response['data']['message'] = $this->response['message'];
            $this->response["data"]['errors'] = $this->getErrorValidator($errors);
            
            $statusParams["data"] = $errors;
            $statusParams["response"] = $this->response;

            $this->statusTable->insert([
                "name" =>"errorcreated" .$table,
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

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $params = $this->getParams($request, $item);
                $tabItem = $this->table->find($id);
                // var_dump($tabItem); die();
                $params['in_stock'] += $tabItem->inStock;
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
                    "new" => $params
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

    protected function getParamForm($entity)
    {
        $tab = [];
        $tabUser = [];
        $keysUser = ['firstname', 'lastname', 'email', 'phone', 'username', 'usersId'];
        $tabProducts = [];
        $keysProducts = ['', 'price_per_unit', 'basic_unit', 'categories_id', 'name', 'products_id'];
        if ($entity instanceof QueryResult) {
            foreach ($entity->getRecords() as $k => $v) {
                foreach ($v as $key => $value) {
                    if (!is_null($value)) {
                        if (array_search($key, $keysUser)) {
                            $tabUser[$key] = $value;
                        } elseif(array_search($key, $keysProducts)){
                            $tabProducts[$key] = $value;
                        } else {
                            $tab[$key] = $value;
                        }
                    }
                }
                $tab["product"] = $tabProducts;
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
                    } elseif(array_search($key, $keysProducts)){
                        $tabProducts[$key] = $value;
                    } else {
                        $tab[$key] = $value;
                    }
                }
            }
            $tab["product"] = $tabProducts;
            $tab["user"] = $tabUser;
            $response['data'] = $tab;
        }
        return empty($tab) ? null : $response;
    }


    protected function getNewEntity()
    {
        $post = new StockEntity();
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
                    'enterprises_id',
                    'products_id',
                    'users_id',
                    'in_stock'
                ]
            );
        }, ARRAY_FILTER_USE_KEY);
        $params['created_at'] = $post->created_at;
        return $params;
    }

    protected function getValidator($request): Validator
    {
        $validator = parent::getValidator($request)
            ->required('products_id', 'users_id', 'in_stock', 'enterprises_id')
            ->notEmpty('products_id', 'users_id', 'in_stock', 'enterprises_id');
        return $validator;
    }
    
}