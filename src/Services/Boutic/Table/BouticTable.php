<?php

namespace App\Services\Boutic\Table;

use Controllers\Database\Table;

class LoginTable extends Table
{
    protected $table='boutics';
    protected $entity = BouticEntity::class;
}