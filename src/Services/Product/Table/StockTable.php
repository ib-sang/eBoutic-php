<?php 

namespace App\Services\Product\Table;

use App\Services\Product\Enity\StockEntity;
use Controllers\Table\FindTable;

class StockTable extends FindTable {

    protected $table='stocks';
    protected $entity = StockEntity::class;
}