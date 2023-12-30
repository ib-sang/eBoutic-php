<?php 

namespace App\Services\Personnels\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Auth\Table\UserTable;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Table\PersonnelTable;
use Controllers\Action\RouterAwareAction;
use Controllers\Renderer\RendererInterface;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class PersonnelInitActionPost{

    private $renderer;
    private $statusTable;
    private $enterprise;
    private $personnel;
    private $table;
    private $auth;
    private $response = [
        "status" => 500,
        "message" => "erreur de connexion à la base de donnée",
        "data" => []
    ];

    use RouterAwareAction;

    public function __construct(RendererInterface $renderer, DatabaseAuth $auth, UserTable $table, StatusTable $statusTable,  EnterpriseTable $enterprise, PersonnelTable $personnel)
    {
        $this->statusTable = $statusTable;
        $this->enterprise = $enterprise;
        $this->personnel = $personnel;
        $this->renderer = $renderer;
        $this->table = $table;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request)
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
        if ($request->getMethod() === 'POST') {
            $userInit = $this->table->find($id);
            $params["password"] = "123";
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
            $this->table->update($id, $params);
            $this->response['status'] = 201;
            $this->response['data']['status'] = 201;
            $this->response['message'] = $table.' Init Successfull!';
            $this->response['data']['message'] = $table.' Init Successfull!';

            $this->response['data'] = [
                'message' => $this->response['message'],
                'personnels' => $userInit,
                'request' => [
                    'message' => 'ALL_ENTITY',
                    'type' => 'GET',
                    'url' => 'http:localhost:3000/api/v1/'.$table
                ]
            ];
            // $statusParams["data"] = $userInit;
            // $statusParams["response"] = $this->response;

            // $this->statusTable->insert([
            //     "name" =>"created" .$table,
            //     "description" => "un entré de données de la table: ".$table.", a été ajouté appélé par " .$user->username,
            //     "status" => json_encode($statusParams),
            //     "enterprise_id" => $enterprise->id,
            //     "created_at" => new DateTime()
            // ]);
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

        // $statusParams["response"] = $this->response;

        // $this->statusTable->insert([
        //     "name" =>"errorcreated" .$table,
        //     "description" => "Il y'a eu une erreur de la table: ".$table.", appélé par " .$user->username,
        //     "status" => json_encode($statusParams),
        //     "enterprise_id" => $enterprise->id,
        //     "created_at" => new DateTime()
        // ]);

        return $this->renderer->renderapi(
            $this->response["status"],
            $this->response['data'],
            $this->response['message']
        );
    }

    private function getEnterprise(int $id, ?int $idEn = null)
    {
        
        if (!is_null($idEn)) {
            $result = $this->enterprise->find($idEn);
        } else {
            $result = $this->enterprise->findBy('users_id', $id);
        }
        return $result;
    }

}