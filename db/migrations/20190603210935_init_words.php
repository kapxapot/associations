<?php

use Phinx\Migration\AbstractMigration;

class InitWords extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('words');

        $table
            ->addColumn('word', 'string', ['limit' => 250])
            ->addColumn('language_id', 'integer')
            ->addColumn('word_bin', 'string', ['limit' => 250, 'collation' => 'utf8_bin'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addForeignKey('language_id', 'languages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
