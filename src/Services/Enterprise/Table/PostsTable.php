<?php

namespace App\Services\Enterprise\Table;

use App\Services\Enterprise\Entity\PostsEntity;
use Controllers\Table\FindTable;

class PostsTable extends FindTable
{
    protected $table = 'posts';
    protected $entity = PostsEntity::class;
}
