<?php 

namespace App\Services\Product\Table;

use App\Services\Product\Entity\StockEntity;
use Controllers\Table\FindTable;

class StockTable extends FindTable {

    protected $table='stocks';
    protected $entity = StockEntity::class;
}