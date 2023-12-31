<?php 

namespace App\Services\Product\Table;

use App\Services\Product\Entity\ProductEntity;
use Controllers\Table\FindTable;

class ProductTable extends FindTable {

    protected $table='products';
    protected $entity = ProductEntity::class;
}