<?php

use Phinx\Migration\AbstractMigration;

class AddMetaToWords extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('words');

        $table
            ->addColumn('meta', 'text', ['null' => true])
            ->save();
    }
}
