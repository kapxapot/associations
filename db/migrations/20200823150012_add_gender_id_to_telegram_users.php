<?php

use Phinx\Migration\AbstractMigration;

class AddGenderIdToTelegramUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('telegram_users');

        $table
            ->addColumn('gender_id', 'integer', ['null' => true])
            ->addForeignKey('gender_id', 'genders', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->save();
    }
}
