<?php

use Phinx\Migration\AbstractMigration;

class AddMetaToTelegramUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('telegram_users');

        $table
            ->addColumn('meta', 'text', ['null' => true])
            ->save();
    }
}
