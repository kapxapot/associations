<?php

use Phinx\Migration\AbstractMigration;

class InitTelegramUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('telegram_users');

        $table
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('telegram_id', 'biginteger')
            ->addColumn('username', 'string', ['limit' => 32, 'null' => true])
            ->addColumn('first_name', 'string', ['limit' => 64, 'null' => true])
            ->addColumn('last_name', 'string', ['limit' => 64, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
