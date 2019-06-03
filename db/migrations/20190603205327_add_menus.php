<?php

use Phinx\Migration\AbstractMigration;

class AddMenus extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('menus');

        $table
            ->insert([
                [
                    'id' => 1,
                    'link' => '/',
                    'text' => 'Игра',
                    'position' => 1,
                ],
                [
                    'id' => 2,
                    'link' => '/words',
                    'text' => 'Слова',
                    'position' => 2,
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this->getQueryBuilder()
            ->delete('menus')
            ->where(['id' => 2])
            ->execute();

        $this->getQueryBuilder()
            ->delete('menus')
            ->where(['id' => 1])
            ->execute();
    }
}
