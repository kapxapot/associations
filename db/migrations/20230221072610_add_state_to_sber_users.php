<?php

use Phinx\Migration\AbstractMigration;

class AddStateToSberUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('sber_users');

        $table
            ->addColumn('state', 'text', ['null' => true])
            ->save();
    }
}
