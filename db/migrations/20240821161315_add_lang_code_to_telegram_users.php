<?php

use Phinx\Migration\AbstractMigration;

class AddLangCodeToTelegramUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('telegram_users');

        $table
            ->addColumn('lang_code', 'string', ['null' => true, 'limit' => 10])
            ->save();
    }
}
