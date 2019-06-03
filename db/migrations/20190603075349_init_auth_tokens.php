<?php

use Phinx\Migration\AbstractMigration;

class InitAuthTokens extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('auth_tokens');

        $table
            ->addColumn('user_id', 'integer')
            ->addColumn('token', 'string', ['limit' => 32])
            ->addColumn('expires_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
