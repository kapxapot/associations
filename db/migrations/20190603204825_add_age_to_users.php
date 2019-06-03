<?php

use Phinx\Migration\AbstractMigration;

class AddAgeToUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');

        $table
            ->addColumn('age', 'integer', ['after' => 'email'])
            ->save();
    }
}
