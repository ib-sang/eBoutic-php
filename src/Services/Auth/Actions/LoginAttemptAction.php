<?php

namespace App\Services\Auth\Actions;

use DateTime;
use App\Services\Auth\DatabaseAuth;
use Controllers\Database\QueryResult;
use App\Services\Auth\Entity\UserEntity;

use Controllers\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginAttemptAction
{
    private $renderer;

    private $auth;

    private $cashTable;

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
        // CashTable $cashTable
    ) {
        // $this->cashTable = $cashTable;
        $this->renderer = $renderer;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $items = $this->getNewEntity();
        $params = $this->getParams($request, $items);
        
        $identify = $params['username'];
        $password = $params['password'];
        $user = $this->auth->login($identify, $password);
        if ($user) {
            $path = [];
            
            $role = json_decode($user['user']->roles)->role;
            $path['roles'] = $role;
            $path['username'] =$user['user']->username;
            // $path['roles'] = explode('_', $role)[1];
            $id = $user['user']->id;
            $path['id']= $id;
            
            if (array_search('role_agent', $role) && !$this->is_opened($this->cashTable->findLatestBy('users_id', $id), $id)) {

                $this->response['data']['message'] = "Votre acces est bloque jusqu'à 3h.";
                $this->response['status'] = 404;
                $this->response['data']['request'] = [
                    'type' => 'POST',
                    'url' => 'http:localhost:3000/api/v1/login',
                    'data' => ['username', 'password']
                ];
                return $this->renderer->renderapi(
                    $this->response['status'],
                    $this->response['data'],
                    $this->response['message']
                );
            }
            if (array_search('role_agent', $role)) {
                $this->response['data']['cash'] = $this->cashTable->findLatestBy('users_id', $id);
            }
            
            $this->response['status'] = 201;
            $this->response['data']['message'] = 'Auth successful';

            $this->response['data']['user'] = $path;

            $this->response['data']['token'] = $user['token'];
            $this->response['data']['enterprise'] = $user['enterprise'];
            $this->response['data']['agence'] = $user['agence'];
            
            $this->response['data']['request'] = [
                'type' => 'GET',
                'url' => 'http:localhost:3000/api/v1/login',
            ];
            return $this->renderer->renderapi(
                $this->response['status'],
                $this->response['data'],
                $this->response['message']
            );
        } else {
            $this->response['data']['message'] = 'Auth failed.';
            $this->response['status'] = 404;
            $this->response['data']['request'] = [
                'type' => 'POST',
                'url' => 'http:localhost:3000/api/v1/login',
                'data' => ['username', 'password']
            ];
            return $this->renderer->renderapi(
                $this->response['status'],
                $this->response['data'],
                $this->response['message']
            );
        }
        return $this->renderer->renderapi(
            $this->response['status'],
            $this->response['data'],
            $this->response['message']
        );
    }

    private function formParams($entity): array
    {
        $tab = [];
        if ($entity instanceof QueryResult){
            $entity = $entity->getRecords();
        }
        foreach ($entity as $key => $value) {
            if (!is_null($value)) {
                $tab[$key] = $value;
            }
        }

        return $tab;
    }

    private function is_opened($lastStatus, int $id): bool
    {
        $total = 0;
        // var_dump($lastStatus); die();
        $date = new DateTime();
        if ($lastStatus) {
            if ($lastStatus->isOpen) {
                $up = $lastStatus->createdUp;
                if (!is_null($up)) {
                    $date = new DateTime($up);
                    $d = $date->format("l");
                    $h = $date->format('H:i');
                    $time = strtotime($lastStatus->time);
                    $timeplus = 6 * 60 * 60 + 30*60 + 00;
                    return strtotime($h) + $timeplus >= $time ;
                }
                return true;
            } else {
                $up = $lastStatus->createdUp;
                $date = new DateTime($up);
                $d = $date->format("l");
                $h = $date->format('H:i');
                $time = strtotime($lastStatus->time) ;
                $timeplus = 0 * 60 * 60 + 0*60 + 00;
                // 06:30 after close a cash
                
                $bool = strtotime($h) <= $time + $timeplus;
                // var_dump($h);
                // var_dump($bool);
                if ($bool = true) {
                    $string  = "Ouverture de la caisse avec une somme de 0 Fanc CFA à ".
                    (new DateTime())->format('Y-m-d H:i:s').", l'heure au Mali";
                    $paramsStatus = [
                        'name' => 'openingcash',
                        'description' => $string,
                        'start_total' => $total,
                        'is_open' => true,
                        'created_at' => new DateTime(),
                        'others' => json_encode([]),
                        'users_id' => $id
                        ];
                    $this->cashTable->insert($paramsStatus);
                    return true;
                }
                return false;
            }
        } else {
            $string  = "Ouverture de la caisse avec une somme de 0 Fanc CFA à ".
            $date->format('Y-m-d H:i:s').", l'heure au Mali";
            $paramsStatus = [
                'name' => 'openingcash',
                'description' => $string,
                'start_total' => $total,
                'is_open' => true,
                'created_at' => new DateTime(),
                'others' => json_encode([]),
                'users_id' => $id
                    ];
            $this->cashTable->insert($paramsStatus);
            return true;
        }
    }

    private function getNewEntity()
    {
        $post = new UserEntity();
        $post->created_at = new \DateTime();
        return $post;
    }

    private function getParams(ServerRequestInterface $request, $post)
    {
        
        $data = json_decode(file_get_contents("php://input"), true);
        $params = array_merge($this->getParseBodyJSON($request));
        $params = array_filter($params, function ($keys) {
            return in_array($keys, ['username', 'password', 'device', 'floate']);
        }, ARRAY_FILTER_USE_KEY);
        
        return $params;
        return array_merge($params, ['number_connexion' => 0, 'login_date' => date('Y-m-d H:i:s')]);
    }

    private function getParseBodyJSON(ServerRequestInterface $request)
    {
        $tab = [];
        foreach (json_decode($request->getBody()->getContents()) as $k => $v) {
            $tab[$k] = $v;
        }
        return $tab;
    }
}
