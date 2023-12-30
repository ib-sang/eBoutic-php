<?php

namespace App\Services\Enterprise\Table;

use App\Services\Enterprise\Entity\StatusEntity;
use Controllers\Table\FindTable;

class StatusTable extends FindTable
{
    protected $table = 'statuses';
    protected $entity = StatusEntity::class;
}
