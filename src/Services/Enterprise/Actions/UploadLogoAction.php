<?php 

namespace App\Services\Enterprise\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Enterprise\EnterpriseUpload;
use App\Services\Enterprise\Entity\EnterpriseEntity;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Table\PersonnelTable;
use Controllers\Database\Hydrator;
use Controllers\Database\QueryResult;
use Controllers\Renderer\RendererInterface;
use Controllers\Validator;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class UploadLogoAction {

    private $enterpriseUpload;
    private $renderer;
    private $table;
    private $response = [
        "status" => 500,
        "message" => "erreur de connexion à la base de donnée",
        "data" => null
    ];

    private $auth;
    private $statusTable;
    private $personnel;

    /**
     * __construct
     *
     * @param  RendererInterface $renderer
     * @param  DatabaseAuth $auth
     *
     * @return void
     */
    public function __construct(
        EnterpriseUpload $enterpriseUpload,
        RendererInterface $renderer,
        PersonnelTable $personnel,
        StatusTable $statusTable,
        DatabaseAuth $auth,
        EnterpriseTable $table
    ) {
        $this->enterpriseUpload = $enterpriseUpload;
        $this->statusTable = $statusTable;
        $this->personnel = $personnel;
        $this->renderer = $renderer;
        $this->table = $table;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request)
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

    private function getParamForm($entity)
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


    private function getNewEntity()
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
                    'image'
                    ]
            );
        }, ARRAY_FILTER_USE_KEY);
        $params['created_up'] = $post->created_at;

        return $params;
    }

    private function getValidator($request): Validator
    {
        $validator = new Validator(array_merge($this->getParseBodyJSON($request), $request->getUploadedFiles()));
        return $validator;
    }

    private function getErrorValidator(array $errors):array
    {
        $errorParams =[];
        foreach ($errors as $key => $value) {
            $errorParams[$key] = $errors[$key]->__toString();
        };
        return $errorParams;
    }

    private function getEnterprise(int $id, ?int $idEn = null)
    {
        
        if (!is_null($idEn)) {
            $result = $this->table->find($idEn);
        } else {
            $result = $this->table->findBy('users_id', $id);
        }
        return $result;
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