<?php

namespace App\Services\Boutic\Table;

use App\Services\Boutic\Entity\BouticEntity;
use Controllers\Table\FindTable;

class BouticTable extends FindTable
{
    protected $table='boutics';
    protected $entity = BouticEntity::class;
}