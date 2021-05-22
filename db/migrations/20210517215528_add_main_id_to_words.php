<?php

use Phinx\Migration\AbstractMigration;

class AddMainIdToWords extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('words');

        $table
            ->addColumn('main_id', 'integer', ['null' => true])
            ->save();
    }
}
