<?php

use Phinx\Migration\AbstractMigration;

class AddOverrideColumnsToAssociations extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('associations');

        $table
            ->addColumn('disabled', 'boolean', ['default' => false])
            ->addColumn('disabled_updated_at', 'timestamp', ['null' => true])
            ->save();
    }
}
