<?php

use Phinx\Migration\AbstractMigration;

class ChangeTelegramUsers extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('telegram_users');

        $table
            ->changeColumn('username', 'string', ['limit' => 32, 'null' => true])
            ->changeColumn('first_name', 'string', ['limit' => 64, 'null' => true])
            ->changeColumn('last_name', 'string', ['limit' => 64, 'null' => true])
            ->save();
    }

    public function down()
    {
        $table = $this->table('telegram_users');

        $table
            ->changeColumn('username', 'text', ['null' => true])
            ->changeColumn('first_name', 'text', ['null' => true])
            ->changeColumn('last_name', 'text', ['null' => true])
            ->save();
    }
}
