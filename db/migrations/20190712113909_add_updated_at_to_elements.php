<?php

use Phinx\Migration\AbstractMigration;

class AddUpdatedAtToElements extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('words');

        $table
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->save();

        $table = $this->table('associations');

        $table
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->save();
    }
}
