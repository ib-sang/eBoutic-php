<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StatusUserCreate extends AbstractMigration
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
        $tab = $this->table('statususers');
        $tab->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('published', 'boolean', ['default'=>true])
            ->addColumn('is_working', 'boolean', ['default'=>true])
            ->addColumn('created_at', 'datetime', ['null' => true])
              ->addColumn('created_up', 'datetime', ['null' => true])
              ->addColumn('enterprises_id', 'integer', ['null'=>true])
              ->addForeignKey('enterprises_id', 'enterprises', 'id', ['delete'=>'SET NULL'])
              ->addColumn('users_id', 'integer', ['null'=>true])
              ->addForeignKey('users_id', 'users', 'id', ['delete'=>'SET NULL'])
              ->create();
    }
}
