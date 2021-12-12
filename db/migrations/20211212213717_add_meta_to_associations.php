<?php

use Phinx\Migration\AbstractMigration;

class AddMetaToAssociations extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('associations');

        $table
            ->addColumn('meta', 'text', ['null' => true])
            ->save();
    }
}
