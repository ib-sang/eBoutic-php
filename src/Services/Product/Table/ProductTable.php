<?php 

namespace App\Services\Product\Table;

use App\Services\Product\Enity\ProductEntity;
use Controllers\Table\FindTable;

class ProductTable extends FindTable {

    protected $table='products';
    protected $entity = ProductEntity::class;
}