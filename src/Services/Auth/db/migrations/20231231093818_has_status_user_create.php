<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class HasStatusUserCreate extends AbstractMigration
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
        $tab = $this->table('hasstatususers');
        $tab->addColumn('published', 'boolean', ['default'=>true])
            ->addColumn('status_start', 'datetime', ['null' => true])
            ->addColumn('status_end', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('created_up', 'datetime', ['null' => true])
            ->addColumn('statususers_id', 'integer', ['null'=>true])
            ->addForeignKey('statususers_id', 'statususers', 'id', ['delete'=>'SET NULL'])
            ->addColumn('enterprises_id', 'integer', ['null'=>true])
            ->addForeignKey('enterprises_id', 'enterprises', 'id', ['delete'=>'SET NULL'])
            ->addColumn('is_users', 'integer', ['null'=>true])
            ->addForeignKey('is_users', 'users', 'id', ['delete'=>'SET NULL'])
            ->addColumn('users_id', 'integer', ['null'=>true])
            ->addForeignKey('users_id', 'users', 'id', ['delete'=>'SET NULL'])
            ->create();
    }
}
