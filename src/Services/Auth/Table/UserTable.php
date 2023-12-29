<?php

namespace App\Services\Auth\Table;

use Controllers\Table\FindTable;
use App\Services\Auth\Entity\UserEntity;

class UserTable extends FindTable
{

    protected $table='users';
    protected $entity=UserEntity::class;
}
