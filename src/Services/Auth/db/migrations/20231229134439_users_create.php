<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UsersCreate extends AbstractMigration
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
        $users = $this->table('users');
        $users->addColumn('username', 'string', ['limit' => 255])
              ->addColumn('password', 'string', ['limit' => 255])
              ->addColumn('email', 'string', ['limit' => 100])
              ->addColumn('phone', 'string', ['limit' => 100])
              ->addColumn('firstname', 'string', ['limit' => 30, 'null' => true])
              ->addColumn('lastname', 'string', ['limit' => 30, 'null' => true])
              ->addColumn('sexe', 'string', ['limit' => 30, 'null' => true])
              ->addColumn('adress', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('roles', 'json', ['null' => true])
              ->addColumn('image', 'string', ['null' => true])
              ->addColumn('published', 'boolean', ['default'=>true])
              ->addColumn('created_at', 'datetime')
              ->addColumn('created_up', 'datetime', ['null' => true])
              ->addIndex(['username', 'email'], ['unique' => true])
              ->create();
    }
}
