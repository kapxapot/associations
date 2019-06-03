<?php

use Phinx\Migration\AbstractMigration;

class InitLanguages extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('languages');

        $table
            ->addColumn('name', 'string', ['limit' => 250])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $table
            ->insert([
                [
                    'id' => 1,
                    'name' => 'Русский',
                    'created_by' => 1,
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this->table('languages')->drop()->save();
    }
}
