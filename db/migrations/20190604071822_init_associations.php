<?php

use Phinx\Migration\AbstractMigration;

class InitAssociations extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('associations');

        $table
            ->addColumn('language_id', 'integer')
            ->addColumn('first_word_id', 'integer')
            ->addColumn('second_word_id', 'integer')
            ->addColumn('word_bin', 'string', ['limit' => 250, 'collation' => 'utf8_bin'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->addForeignKey('language_id', 'languages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('first_word_id', 'words', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('second_word_id', 'words', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('deleted_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
