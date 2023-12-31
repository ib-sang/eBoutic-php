<?php

namespace App\Services\Role\Table;

use App\Services\Role\Entity\RoleEntity;
use Controllers\Table\FindTable;

class RoleTable extends FindTable
{
    protected $table = 'roles';
    protected $entity = RoleEntity::class;
}