<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RoleHasCreate extends AbstractMigration
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
        $table = $this->table('hasroles');
        $table->addColumn('has', 'boolean', ['default'=>true])
            ->addColumn('published', 'boolean', ['default'=>true])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_up', 'datetime', ['null' => true])
            ->addColumn('roles_id', 'integer', ['null'=>true])
            ->addForeignKey('roles_id', 'roles', 'id', ['delete'=>'SET NULL', 'update'=> 'NO_ACTION'])
            ->addColumn('users_id', 'integer', ['null'=>true])
            ->addForeignKey('users_id', 'users', 'id', ['delete'=>'SET NULL', 'update'=> 'NO_ACTION'])
            ->create();
    }
}
