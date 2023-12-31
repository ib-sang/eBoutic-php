<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ProductCreate extends AbstractMigration
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
        $table = $this->table('products');
        $table->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('description', 'string', ['limit' => 255, 'null' =>true])
            ->addColumn('price_per_unit', 'biginteger', [ 'null' =>true])
            ->addColumn('basic_unit', 'biginteger', ['null' =>true])
            ->addColumn('promo_unit', 'biginteger', ['null' =>true])
            ->addColumn('active_for_sale', 'boolean', ['default'=>true])
            ->addColumn('published', 'boolean', ['default'=>true])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_up', 'datetime', ['null' => true])
            ->addColumn('boutics_id', 'integer', ['null'=>true])
            ->addForeignKey('boutics_id', 'boutics', 'id', ['delete'=>'SET NULL', 'update'=> 'NO_ACTION'])
            ->addColumn('categories_id', 'integer', ['null'=>true])
            ->addForeignKey('categories_id', 'categories', 'id', ['delete'=>'SET NULL', 'update'=> 'NO_ACTION'])
            ->addColumn('users_id', 'integer', ['null'=>true])
            ->addForeignKey('users_id', 'users', 'id', ['delete'=>'SET NULL', 'update'=> 'NO_ACTION'])
            ->create();
    }
}
