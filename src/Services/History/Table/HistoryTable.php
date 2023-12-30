<?php

namespace App\Services\History\Table;

use App\Services\History\Entity\HistoryEntity;
use Controllers\Table\FindTable;

class HistoryTable extends FindTable
{
    protected $table = 'statuses';
    protected $entity = HistoryEntity::class;
}