<?php

use Phinx\Migration\AbstractMigration;

class AddGoogleIdToUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');

        $table
            ->addColumn('google_id', 'string', ['limit' => 64, 'null' => true, 'after' => 'email'])
            ->addIndex(['google_id'], ['unique' => true])
            ->update();
    }
}
