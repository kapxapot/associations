<?php

use Phinx\Migration\AbstractMigration;

class UpdateRoles extends AbstractMigration
{
    public function up()
    {
        $this->getQueryBuilder()
            ->update('roles')
            ->set('name', 'Модератор')
            ->where(['tag' => 'editor'])
            ->execute();
        
        $this->getQueryBuilder()
            ->update('roles')
            ->set('name', 'Игрок')
            ->where(['tag' => 'author'])
            ->execute();
    }

    public function down()
    {
        $this->getQueryBuilder()
            ->update('roles')
            ->set('name', 'Редактор')
            ->where(['tag' => 'editor'])
            ->execute();
        
        $this->getQueryBuilder()
            ->update('roles')
            ->set('name', 'Автор')
            ->where(['tag' => 'author'])
            ->execute();
    }
}
