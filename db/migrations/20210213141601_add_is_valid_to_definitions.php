<?php

use Phinx\Migration\AbstractMigration;

class AddIsValidToDefinitions extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('definitions');

        $table
            ->addColumn('valid', 'boolean', ['default' => false])
            ->save();
    }
}
