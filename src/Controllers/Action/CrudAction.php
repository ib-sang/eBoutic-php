<?php

namespace Controllers\Action;

use App\Services\Auth\DatabaseAuth;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Table\PersonnelTable;
use Controllers\Validator;
use Controllers\Database\Hydrator;
use Controllers\Renderer\RendererInterface;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class CrudAction
{

    protected $renderer;
    protected $table;
    protected $auth;
    protected $response = [
        "status" => 500,
        "message" => "erreur de connexion à la base de donnée",
        "data" => []
    ];

    protected $statusTable;
    protected $enterprise;
    protected $personnel;

    use RouterAwareAction;

    public function __construct(RendererInterface $renderer, DatabaseAuth $auth, $table, StatusTable $statusTable, EnterpriseTable $enterprise, PersonnelTable $personnel)
    {
        $this->statusTable = $statusTable;
        $this->enterprise = $enterprise;
        $this->personnel = $personnel;
        $this->renderer = $renderer;
        $this->auth = $auth;
        $this->table = $table;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        if ($request->getMethod() === "DELETE" || array_key_exists('_method', $request->getParsedBody())) {
            return $this->delete($request);
        }
        if (substr((string)$request->getUri(), -3) === 'new') {
            return $this->create($request);
        }

        if ($request->getAttribute('id') && ($request->getMethod() ==="POST" || $request->getMethod() ==="PUT")) {
            return $this->edit($request);
        }
        if ($request->getAttribute('id')) {
            return $this->show($request);
        }
        return $this->index($request);
    }


    public function show(ServerRequestInterface $request)
    {
        $id = $request->getAttribute('id');
        $user = $this->auth->getUser();

        $table = $this->table->getTable();

        if ($id) {
            $data = $this->table->find($id);
            if ($data !==false) {
                $tab = $this->getParamForm($data);
                $this->response['status'] = 201;
                $this->response['data'][$table] = $tab;
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
        $user = $this->auth->getUser();
        $params = $request->getQueryParams();
        $table = $this->table->getTable();
        // $statusParams = [
        //     "message" => "La liste de données dans la base",
        //     "user" => $user,
        //     "table" => $table,
        // ];
        // $roleUser = json_decode($user->roles)->role;
        // $userId = $user->id;

        // if (array_search('role_users', $roleUser)!==false && !(array_search('role_admin', $roleUser)!==false)) {
        //     $personnel = $this->personnel->findBy('users_id', $userId);
        //     $enterprise = $this->getEnterprise($userId, $personnel->enterpriseId);
        // }

        // if (array_search('role_admin', $roleUser)!==false) {
        //     // $enterprise = $this->getEnterprise($userId);
        //     $items = $this->table->findAllPublic()->paginate(70, $params['p'] ?? 1);
        // }else{
        //     $items = $this->table->findAllPublicBy('users_id', $userId)->paginate(70, $params['p'] ?? 1);
        // }

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
        $errors = '';
        $table = $this->table->getTable();
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
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $params = $this->getParams($request, $item);
                // if ($table == 'depenses') {
                //     if ($params['busies_id']) {
                        
                //     }    
                
                // }
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
                        "description" => "un entré de données de la table: ".$table.", a été ajouté appélé par ",
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
            "description" => "Il y'a eu une erreur de la table: ".$table.", appélé par ",
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

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $params = $this->getParams($request, $item);
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

    public function delete(ServerRequestInterface $request)
    {
        $id = $request->getAttribute('id');
        $table = $this->table->getTable();
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
        if ($id) {
            $tab = $this->table->find($id);
            $this->table->delete($id);
            $this->response['status'] = 201;
            $this->response['message'] = $table.' Deleted Successfull!';
            $this->response['data'] = [
                    'status' => $this->response['status'],
                    'message' => $this->response['message'],
                    'request' => [
                        'message' => 'ALL_ENTITY',
                        'type' => 'GET',
                        'url' => 'http:localhost:3000/api/v1/'.$table,
                    ]
                ];

                $statusParams["data"] = $tab;
                $statusParams["response"] = $this->response;

                $this->statusTable->insert([
                    "name" =>"delete" .$table,
                    "description" => "un entré de données de la table: ".$table.", a été supprimé appélé par ",
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
            'message' => 'DELETE_ENTERPRISES',
            'type' => 'DELETE',
            'url' => 'http://localhost:3000/'.$table.'/'.$id,
        ];
        $statusParams["response"] = $this->response;

        $this->statusTable->insert([
            "name" =>"errordelete" .$table,
            "description" => "Il y'a eu une erreur de la table: ".$table.", appélé par " ,
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

    protected function getParams(ServerRequestInterface $request, $items)
    {
        return  array_filter(
            array_merge(
                $this->getParseBodyJSON($request),
                $request->getUploadedFiles(),
                ['created_at' => $items->created_at]
            ),
            function ($keys) {
                return in_array($keys, []);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function formParams(array $params):array
    {
        return $params;
    }

    protected function getNewEntity()
    {
        return [];
    }

    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return new Validator(array_merge($this->getParseBodyJSON($request), $request->getUploadedFiles()));
    }

    protected function getErrorValidator(array $errors):array
    {
        $errorParams =[];
        foreach ($errors as $key => $value) {
            $errorParams[$key] = $errors[$key]->__toString();
        };
        return $errorParams;
    }

    protected function getParamForm($entity)
    {
        return [];
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

    protected function getEnterprise(int $id, ?int $idEn = null)
    {
        
        if (!is_null($idEn)) {
            $result = $this->enterprise->find($idEn);
        } else {
            $result = $this->enterprise->findBy('users_id', $id);
        }
        return $result;
    }
    
}
