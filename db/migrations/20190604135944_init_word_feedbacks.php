<?php

use Phinx\Migration\AbstractMigration;

class InitWordFeedbacks extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('word_feedbacks');

        $table
            ->addColumn('word_id', 'integer')
            ->addColumn('dislike', 'boolean', ['default' => false])
            ->addColumn('typo', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('duplicate_id', 'integer', ['null' => true])
            ->addColumn('mature', 'boolean', ['default' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer')
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('word_id', 'words', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('duplicate_id', 'words', 'id', ['delete' => 'SET NULL', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
