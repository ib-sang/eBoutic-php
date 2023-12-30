<?php 

namespace App\Services\History\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\History\Table\HistoryTable;
use App\Services\Personnels\Table\PersonnelTable;
use Controllers\Action\RouterAwareAction;
use Controllers\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class StatusIndexAction{

    private $renderer;
    private $table;
    private $auth;
    private $response = [
        "status" => 500,
        "message" => "erreur de connexion à la base de donnée",
        "data" => []
    ];

    private $statusTable;
    private $enterprise;
    private $personnel;
    private $agenceTable;

    use RouterAwareAction;

    public function __construct(
        RendererInterface $renderer,
        DatabaseAuth $auth,
        HistoryTable $table,
        StatusTable $statusTable,
        EnterpriseTable $enterprise,
        PersonnelTable $personnel,
        // AgenceTable $agenceTable
        )
    {
        $this->statusTable = $statusTable;
        // $this->agenceTable = $agenceTable;
        $this->enterprise = $enterprise;
        $this->personnel = $personnel;
        $this->renderer = $renderer;
        $this->auth = $auth;
        $this->table = $table;
    }

    public function __invoke(ServerRequestInterface $request)
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
        //     $enterprise = $this->getEnterprise($userId);
        // }
        $currentPage = $params['p'] ?? 1;
        $items = $this->table->findAllPublic()->paginate(70, $currentPage);
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

    private function getParamForm($entity)
    {
        $tab = [];
        $agence = [];
        $tabUser = [];
        $keysUser = ['', 'firstname', 'lastname', 'phone'];
        foreach ($entity->getRecords() as $k => $v) {
            foreach ($v as $key => $value) {
                if (!is_null($value)) {
                    if (array_search($key, $keysUser)) {
                        $tabUser[$key] = $value;
                    } else {
                        $tab[$key] = $value;
                    }
                    if ($key == 'status') {
                        $tab[$key] = json_decode($value);
                    }
                    // if($key == 'agence_id' || $key == 'agenceId'){
                    //     $agence = $this->agenceTable->find($value);
                    // }
                }
            }
            $tab["user"] = $tabUser;
            $tab["agence"] = $agence;
            $response['data'][$k] = $tab;
        }
        
        
        return empty($tab) ? null : $response;
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