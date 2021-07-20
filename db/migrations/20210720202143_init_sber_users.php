<?php

use Phinx\Migration\AbstractMigration;

class InitSberUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('sber_users');

        $table
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('sber_id', 'string', ['limit' => 256])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
