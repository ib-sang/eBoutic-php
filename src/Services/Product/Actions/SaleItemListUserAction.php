<?php 


namespace App\Services\Product\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Product\Table\SaleProductTable;
use Controllers\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class SaleItemListUserAction
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
        SaleProductTable $table
    ) {
        $this->table = $table;
        $this->renderer = $renderer;
    }
    
    public function __invoke(ServerRequestInterface $request)
    {
        $id = $request->getAttribute('id');
        // $user = $this->auth->getUser();
        $table = $this->table->getTable();
        if ($id) {
            // var_dump($id); die();
            $data = $this->table->findByUserSalesAll('enterprises_id', $id)->fetchAll();
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

    private function getParamForm($entity)
    {
        $tab = [];
        $keysProduct = ['', 'name', 'price_per_unit', 'basic_unit'];
        $keysUser = ['', 'firstname', 'lastname', 'phone'];
        $tabProduct = [];
        $tabUser = [];
        foreach ($entity->getRecords() as $k => $v) {
            // var_dump($v); die();
            foreach ($v as $key => $value) {
                if (!is_null($value)) {
                    if (array_search($key, $keysProduct)) {
                        $tabProduct[$key] = $value;
                    } elseif(array_search($key, $keysUser)){
                        $tabUser[$key] = $value;
                    }else {
                        $tab[$key] = $value;
                    }
                }
            }
            $tab["product"] = $tabProduct;
            $tab["user"] = $tabUser;
            $response['data'][$k] = $tab;
        }
        return empty($tab) ? null : $response;
    }


}