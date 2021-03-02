<?php

use Phinx\Migration\AbstractMigration;

class InitWordOverrides extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('word_overrides');

        $table
            ->addColumn('word_id', 'integer')
            ->addColumn('approved', 'boolean', ['null' => true])
            ->addColumn('mature', 'boolean', ['null' => true])
            ->addColumn('word_correction', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('pos_correction', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('disabled', 'boolean', ['null' => true])
            ->addColumn('created_by', 'integer')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('word_id', 'words', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
