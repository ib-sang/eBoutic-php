<?php 

namespace App\Services\Product\Table;

use App\Services\Product\Entity\StatusSaleEntity;
use Controllers\Table\FindTable;

class StatusSaleTable extends FindTable {

    protected $table='statussale';
    protected $entity = StatusSaleEntity::class;
}