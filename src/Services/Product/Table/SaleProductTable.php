<?php 

namespace App\Services\Product\Table;

use App\Services\Product\Entity\SaleProductEntity;
use Controllers\Table\FindTable;

class SaleProductTable extends FindTable {

    protected $table='salesproduct';
    protected $entity = SaleProductEntity::class;
}