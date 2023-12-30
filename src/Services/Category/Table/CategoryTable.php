<?php 

namespace App\Services\Category\Table;

use App\Services\Category\Enity\CategoryEntity;
use Controllers\Table\FindTable;

class CategoryTable extends FindTable {

    protected $table='categories';
    protected $entity = CategoryEntity::class;
}