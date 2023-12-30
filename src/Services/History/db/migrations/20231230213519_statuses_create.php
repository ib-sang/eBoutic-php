<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StatusesCreate extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('statuses');
        $table->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('description', 'string', ['limit' => 255, 'null' =>true])
            ->addColumn('status', 'string', ['limit' => 255, 'null' =>true])
            ->addColumn('published', 'boolean', ['default'=>true])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_up', 'datetime', ['null' => true])
            ->addColumn('enterprises', 'integer', ['null'=>true])
            ->addForeignKey('enterprises', 'enterprises', 'id', ['delete'=>'SET NULL', 'update'=> 'NO_ACTION'])
            ->create();
    }
}
