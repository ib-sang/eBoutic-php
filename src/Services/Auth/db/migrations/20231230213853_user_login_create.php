<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserLoginCreate extends AbstractMigration
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
        $login = $this->table('loginusers');
        $login->addColumn('device', 'json', ['null' => true])
              ->addColumn('published', 'boolean', ['default'=>true])
              ->addColumn('created_at', 'datetime', ['null' => true])
              ->addColumn('created_up', 'datetime', ['null' => true])
              ->addColumn('login_in', 'datetime', ['null' => true])
              ->addColumn('login_out', 'datetime', ['null' => true])
              ->addColumn('users_id', 'integer', ['null'=>true])
              ->addForeignKey('users_id', 'users', 'id', ['delete'=>'SET NULL'])
              ->create();
    }
}
