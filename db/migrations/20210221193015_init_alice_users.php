<?php

use Phinx\Migration\AbstractMigration;

class InitAliceUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('alice_users');

        $table
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('alice_id', 'string', ['limit' => 64])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
