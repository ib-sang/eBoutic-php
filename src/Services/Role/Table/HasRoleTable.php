<?php

namespace App\Services\Role\Table;

use App\Services\Role\Entity\HasRoleEntity;
use Controllers\Table\FindTable;

class HasRoleTable extends FindTable
{
    protected $table = 'hasroles';
    protected $entity = HasRoleEntity::class;
}