<?php

use Phinx\Migration\AbstractMigration;

class InitDefinitions extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('definitions');

        $table
            ->addColumn('source', 'string', ['limit' => 100])
            ->addColumn('url', 'string', ['limit' => 250])
            ->addColumn('json_data', 'text', ['null' => true])
            ->addColumn('word_id', 'integer')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('word_id', 'words', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
