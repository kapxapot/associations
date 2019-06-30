<?php

use Phinx\Migration\AbstractMigration;

class AddApprovedMatureFields extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('words');

        $table
            ->addColumn('mature', 'boolean', ['default' => false])
            ->addColumn('mature_updated_at', 'timestamp', ['null' => true])
            ->addColumn('approved', 'boolean', ['default' => false])
            ->addColumn('approved_updated_at', 'timestamp', ['null' => true])
            ->save();

        $table = $this->table('associations');

        $table
            ->addColumn('mature', 'boolean', ['default' => false])
            ->addColumn('mature_updated_at', 'timestamp', ['null' => true])
            ->addColumn('approved', 'boolean', ['default' => false])
            ->addColumn('approved_updated_at', 'timestamp', ['null' => true])
            ->save();
    }
}
