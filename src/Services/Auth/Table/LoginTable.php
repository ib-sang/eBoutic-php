<?php

namespace App\Services\Auth\Table;

use App\Services\Auth\Entity\LoginEntity;
use Controllers\Database\Table;

class LoginTable extends Table
{
    protected $table='loginusers';
    protected $entity = LoginEntity::class;
}
