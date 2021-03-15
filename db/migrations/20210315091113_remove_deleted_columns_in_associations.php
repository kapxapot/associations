<?php

use Phinx\Migration\AbstractMigration;

class RemoveDeletedColumnsInAssociations extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('associations');

        $table
            ->dropForeignKey('deleted_by')
            ->removeColumn('deleted_by')
            ->removeColumn('deleted_at')
            ->save();
    }

    public function down()
    {
        $table = $this->table('associations');

        $table
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->addForeignKey('deleted_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->save();
    }
}
