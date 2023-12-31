<?php 

namespace App\Services\Product\Table;

use App\Services\Product\Entity\SaleEntity;
use Controllers\Table\FindTable;

class SaleTable extends FindTable {

    protected $table='sales';
    protected $entity = SaleEntity::class;
}