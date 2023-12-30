<?php

namespace App\Services\Personnels\Table;

use App\Services\Personnels\Entity\PersonnelEntity;
use Controllers\Table\FindTable;

class PersonnelTable extends FindTable
{
    protected $table = 'personnels';
    protected $entity = PersonnelEntity::class;
}
