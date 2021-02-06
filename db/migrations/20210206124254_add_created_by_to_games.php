<?php

use Phinx\Migration\AbstractMigration;

class AddCreatedByToGames extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('games');

        $table
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->save();

        $this->execute('update games set created_by = user_id');
    }

    public function down()
    {
        $table = $this->table('games');

        $table
            ->dropForeignKey('created_by')
            ->removeColumn('created_by')
            ->save();
    }
}
