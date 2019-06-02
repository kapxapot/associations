<?php

use Phinx\Migration\AbstractMigration;

class InitRoles extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('roles');

        $table
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('tag', 'string', ['limit' => 50])
            ->create();

        $table
            ->insert([
                [
                    'id' => 1,
                    'name' => 'Администратор',
                    'tag' => 'admin',
                ],
                [
                    'id' => 2,
                    'name' => 'Редактор',
                    'tag' => 'editor',
                ],
                [
                    'id' => 3,
                    'name' => 'Автор',
                    'tag' => 'author',
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this->table('roles')->drop()->save();
    }
}
