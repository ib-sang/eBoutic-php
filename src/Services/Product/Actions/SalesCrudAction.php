<?php 


namespace App\Services\Product\Actions;

use App\Services\Auth\DatabaseAuth;
use App\Services\Enterprise\Table\EnterpriseTable;
use App\Services\Enterprise\Table\StatusTable;
use App\Services\Personnels\Table\PersonnelTable;
use App\Services\Product\Entity\StockEntity;
use App\Services\Product\Table\SaleProductTable;
use App\Services\Product\Table\SaleTable;
use App\Services\Product\Table\StockTable;
use Controllers\Action\CrudAction;
use Controllers\Database\Hydrator;
use Controllers\Database\QueryResult;
use Controllers\Renderer\RendererInterface;
use Controllers\Validator;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class SalesCrudAction extends CrudAction
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

    private $tableItem;
    private $stockTable;

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
        SaleProductTable $tableItem,
        SaleTable $table,
        StatusTable $statusTable,
        StockTable $stockTable
    ) {
        $this->tableItem = $tableItem;
        $this->stockTable = $stockTable;
        parent::__construct($renderer, $auth, $table, $statusTable, $enterprise, $personnel);
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
                $product_items = $params['product_items'];
                unset($params['product_items']);
                $this->table->insert($params);
                $saleId = $this->table->findLatest()->id;
                $userId = $params['users_id'];
                $enterpriseId = $params['enterprises_id'];
                
                foreach ($product_items as $key => $value) {
                    $paramProduct = [
                        "users_id" => $userId,
                        "price_per_unit" => $value['price_per_unit'],
                        "price" => $value['price'],
                        "quantity_sold" => $value['quantity'],
                        "sales_id" => $saleId,
                        "products_id" => $value['id'],
                        "enterprises_id" => $enterpriseId,
                        "created_at" => new DateTime()
                    ];
                    $stock = $this->stockTable->findBy("products_id", $value['id']);
                    $this->stockTable->update($stock->id, ['in_stock' => (int)$stock->inStock - (int)$value['quantity']]);
                    $this->tableItem->insert($paramProduct);
                }
                
                
                $this->response['status'] = 201;
                $this->response['message'] = 'Agence Registration Successfull!';
                
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

    protected function getParamForm($entity)
    {
        $tab = [];
        $tabUser = [];
        $keysUser = ['firstname', 'lastname', 'email', 'phone', 'username', 'usersId'];
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
                // $tab[$k] = $tab;
                $response['data'][$k] = $tab;
            }
        } else {
            foreach ($entity as $key => $value) {
                if (!is_null($value) && $key !== "password") {
                    // var_dump(array_search($key, $keysUser)); die();
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
                    'sale_amount_quatity',
                    'sale_amount_paid',
                    'enterprises_id',
                    'product_items',
                    'users_id'
                ]
            );
        }, ARRAY_FILTER_USE_KEY);
        $params['created_at'] = $post->created_at;
        return $params;
    }

    protected function getValidator($request): Validator
    {
        $validator = parent::getValidator($request)
            ->required('sale_amount_quatity', 'users_id', 'enterprises_id', 'sale_amount_paid')
            ->notEmpty('sale_amount_quatity', 'users_id', 'enterprises_id', 'sale_amount_paid');
        return $validator;
    }
    
}