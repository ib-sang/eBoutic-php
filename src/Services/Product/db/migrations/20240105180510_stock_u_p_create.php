<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StockUPCreate extends AbstractMigration
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
        $table = $this->table('stocks');
        $table
            ->addColumn('enterprises_id', 'integer', ['null'=>true])
            ->addForeignKey('enterprises_id', 'enterprises', 'id', ['delete'=>'SET NULL', 'update'=> 'NO_ACTION'])
            ->update();
    }
}
