<?php 


namespace App\Services\Product\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Table\PersonnelTable;
use App\Services\Product\Entity\ProductEntity;
use App\Services\Product\Table\ProductTable;
use App\Services\Product\Table\SaleProductTable;
use Controllers\Action\CrudAction;
use Controllers\Database\Hydrator;
use Controllers\Database\QueryResult;
use Controllers\Renderer\RendererInterface;
use Controllers\Validator;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class SaleItemListAction
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
            $items = $this->table->findByUserBouticsAll('boutics_id', $id)->paginate(120, $params['p'] ?? 1);
            $currentPage = $params['p'] ?? 1;
            $count = $items->getNbResults();
            $nbPage = $items->getNbPages();
            if ($items !==false) {
                $tab = $this->getParamForm($items);
                $this->response['status'] = 201;
                $this->response['data']['message'] = "All items on database.";
                $this->response['message'] = "All item on database.";
                $this->response['data']['status'] = 201;
                $this->response['data']['currentPage'] = $currentPage;
                $this->response['data']['nbPage'] = $nbPage;
                $this->response['data']['count'] = $count;
                $this->response['data'][$table] = $tab;

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
        $tabProduct = [];
        $entities = [];
        if ($entity instanceof QueryResult) {
            $entities = $entity->getRecords();
        } else {
            $entities = $entity;
        }
        foreach ($entities as $k => $v) {
            // var_dump($v); die();
            foreach ($v as $key => $value) {
                if (!is_null($value)) {
                    if (array_search($key, $keysProduct)) {
                        $tabProduct[$key] = $value;
                    } else {
                        $tab[$key] = $value;
                    }
                }
            }
            $tab["product"] = $tabProduct;
            $response['data'][$k] = $tab;
        }
        return empty($tab) ? null : $response;
    }


}