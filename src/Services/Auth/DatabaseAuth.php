<?php

namespace App\Services\Auth;

use Controllers\Auth;
use Controllers\Auth\User;
use Controllers\Session\JwtHandler;
use App\Services\Auth\Table\UserTable;
use Controllers\Session\SessionInterface;
use Controllers\Database\NoRecordException;
use DateTime;

class DatabaseAuth extends JwtHandler implements Auth
{
    private $user;
    private $usertable;
    private $session;
    protected $token;
    private $enterprise;
    private $personnel;
    private $guichet;
    private $agence;
    private $loginTable;

    public function __construct(
        UserTable $usertable,
        // AgenceTable $agence,
        // GuichetTable $guichet,
        // LoginTable $loginTable,
        SessionInterface $session,
        // EnterpriseTable $enterprise,
        // PersonnelTable $personnel
    ) {
        // $this->enterprise = $enterprise;
        $this->usertable = $usertable;
        $this->session = $session;
        // $this->agence = $agence;
        // $this->guichet = $guichet;
        // $this->personnel = $personnel;
        // $this->loginTable = $loginTable;
    }

    public function createUser(array $params):?User
    {
        return null;
    }

    public function getUser():?User
    {
        
        if ($this->user) {
            return $this->user;
        }
        $userId = $this->session->get('auth.user');
        if ($userId) {
            try {
                $this->user = $this->usertable->findUser($userId);
                if ($this->user) {
                    $roleUser = json_decode($this->user->roles)->role;
                }
                if (!$this->user) {
                    return null;
                }
                return $this->user;
            } catch (NoRecordException $e) {
                $this->session->delete('auth.user');
                return null;
            }
        }
        return null;
    }

    public function login(string $identify, string $password):?array
    {
        $returnData = [];

        if (empty($identify) || empty($password)) {
            return $returnData;
        }
        
        $user = $this->usertable->findBy('username', $identify) ?? null;
        
        if ($user && password_verify($password, $user->password)) {
            $enterprise = null;
            $agence = null;
            $guichet = null;
            $personnel = null;
            $roleUser = json_decode($user->roles)->role;
            $userId = $user->id;

            if (array_search('role_users', $roleUser)!==false && !(array_search('role_admin', $roleUser)!==false)) {
                $personnel = $this->personnel->findBy('users_id', $userId);
                $enterprise = $this->getEnterprise($userId, $personnel->enterpriseId);
            }

            if (array_search('role_personnel', $roleUser)!== false) {
                $agence = $this->getAgence($personnel->agenceId);
            }

            // var_dump($agence); die();
            if (array_search('role_admin', $roleUser)!==false) {
                $enterprise = $this->getEnterprise($userId);
            }

            $jwt = new JwtHandler();
            $token = $jwt->jwtEncodeData(
                'http://localhost:3000/api/v1/auth/',
                ["user_id"=> $userId]
            );
            
            $returnData = [
                'success' => 1,
                'message' => 'You have successfully logged in.',
                'token' => $token,
                'user' => $user,
                'enterprise' => $enterprise,
                'agence' => $agence
            ];

            $paramsLogin = [
                'login_in' => (new DateTime())->format('Y-m-d H:i:s'),
                'users_id' => $userId,
                'created_at' => new DateTime()
            ];
            // table login status 
            // $this->loginTable->insert($paramsLogin);
            
            $this->session->set('auth.user', $userId);
            return $returnData;
        }
        return null;
    }

    public function signOut(int $id = null):void
    {
        $userId = $id ?? $this->session->get('auth.user');

        $paramsLogin = [
            'login_out' => (new DateTime())->format('Y-m-d H:i:s'),
            'users_id' => $userId,
            'created_at' => new DateTime()
        ];
        $this->loginTable->insert($paramsLogin);
        $this->session->delete('auth.user');
    }

    public function isValid($headers)
    {
        if (array_key_exists('Authorization', $headers) &&
            preg_match('/Bearer\s(\S+)/', $headers['Authorization'][0], $matches)) {
            $data = $this->jwtDecodeData($matches[1]);
            if (isset($data['data']->user_id) &&
                $user = $this->usertable->find($data['data']->user_id)
            ) :
                return [
                    "success" => 1,
                    "user" => $user
                ];
            else :
                return [
                    "success" => 0,
                    "message" => $data['message'],
                ];
            endif;
        } else {
            return [
                "success" => 0,
                "message" => "Token not found in request"
            ];
        }
    }

    private function getGuichet(int $id)
    {
        $result = $this->guichet->find($id);
        if (!$result) {
            return null;
        }
        // var_dump($result); die();
        return $result;
    }

    private function getAgence(int $id)
    {
        $result = $this->agence->find($id);
        if (!$result) {
            return null;
        }
        // var_dump($result); die();
        return $result;
    }

    private function getEnterprise(int $id, ?int $idEn = null)
    {
        $result = [] ;   
        if (!is_null($idEn)) {
            $result = $this->getParmas($this->enterprise->find($idEn));
        } else {
            $result = $this->getParmas($this->enterprise && $this->enterprise->findBy('users_id', $id));
        }
        return $result;
    }

    private function getParmas($entity):array
    {

        $params = [];
        if ($entity) {
            foreach ($entity as $k => $v) {
                if (!is_null($v)) {
                    $params[$k] = $v;
                }
            }
        }
        
        return $params;
    }
}
