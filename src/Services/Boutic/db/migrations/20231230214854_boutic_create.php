<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BouticCreate extends AbstractMigration
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
        $table = $this->table('boutics');
        $table->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('description', 'string', ['limit' => 255, 'null' =>true])
            ->addColumn('type', 'string', ['limit' => 255, 'null' =>true])
            ->addColumn('phone', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('country', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('city', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('towers', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('adress', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('image', 'string', ['null' => true])
            ->addColumn('published', 'boolean', ['default'=>true])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_up', 'datetime', ['null' => true])
            ->addColumn('enterprises_id', 'integer', ['null'=>true])
            ->addForeignKey('enterprises_id', 'enterprises', 'id', ['delete'=>'SET NULL', 'update'=> 'NO_ACTION'])
            ->addColumn('users_id', 'integer', ['null'=>true])
            ->addForeignKey('users_id', 'users', 'id', ['delete'=>'SET NULL', 'update'=> 'NO_ACTION'])
            ->create();
    }
}
