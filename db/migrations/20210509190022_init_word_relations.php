<?php

use Phinx\Migration\AbstractMigration;

class InitWordRelations extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('word_relations');

        $table
            ->addColumn('main_word_id', 'integer')
            ->addColumn('word_id', 'integer')
            ->addColumn('type_id', 'integer')
            ->addColumn('primary', 'boolean', ['default' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addForeignKey('main_word_id', 'words', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('word_id', 'words', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('type_id', 'word_relation_types', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
