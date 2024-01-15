<?php

namespace App\Services\Users\Actions;

use App\Services\Auth\DatabaseAuth;
use Controllers\Validator;
use Controllers\Action\CrudAction;
use App\Services\Auth\Table\UserTable;
use App\Services\Auth\Entity\UserEntity;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Table\PersonnelTable;
use Controllers\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class CrudUserAction extends CrudAction
{

    protected $statusTable;
    protected $renderer;
    protected $enterprise;
    protected $personnel;
    protected $table;
    protected $auth;

    public function __construct(RendererInterface $renderer, DatabaseAuth $auth, UserTable $table, StatusTable $statusTable, EnterpriseTable $enterprise, PersonnelTable $personnel)
    {
        parent::__construct($renderer, $auth, $table, $statusTable, $enterprise, $personnel);
    }

    protected function getNewEntity()
    {
        $post = new UserEntity();
        $post->created_at = new \DateTime();
        return $post;
    }

    protected function getParamForm($entity)
    {
        $tab = [];
        $tabUser = [];
        foreach ($entity as $k => $v) {
            foreach ($v as $key => $value) {
                if (!is_null($value) && $key !== "usersId") {
                    if ($key !== "firstname" && $key !== "lastname") {
                        $tab[$k][$key] = $value;
                    } else {
                        $tabUser[$key] = $value;
                    }
                }
            }
            $tab[$k]['user'] = $tabUser;
        }
        
        return $tab;
    }

    protected function getParams(ServerRequestInterface $request, $post)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $params = array_merge(
            $data,
            $request->getUploadedFiles(),
            ["created_at" => $post->created_at]
        );
        $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        $params =  array_filter($params, function ($keys) {
            return in_array(
                $keys,
                [
                    'username',
                    'password',
                    'phone',
                    'email',
                    'created_at',
                    'firstname',
                    'lastname',
                    'sexe',
                    'adress'
                    ]
            );
        }, ARRAY_FILTER_USE_KEY);
        $roleUser = json_encode(['role' => 'role_master']) ;
        $params['created_at'] = $post->created_at;
        return array_merge($params, ["roles" => $roleUser]);
    }

    protected function getValidator($request): Validator
    {
        $validator = parent::getValidator($request)
            ->required('username', 'password', 'email', 'phone')
            ->notEmpty('username', 'password', 'email', 'phone');
        return $validator;
    }
}
