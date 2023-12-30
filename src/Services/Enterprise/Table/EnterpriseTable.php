<?php

namespace App\Services\Enterprise\Table;

use App\Services\Enterprise\Entity\EnterpriseEntity;
use Controllers\Table\FindTable;

class EnterpriseTable extends FindTable
{
    protected $table = 'enterprises';
    protected $entity = EnterpriseEntity::class;
}
