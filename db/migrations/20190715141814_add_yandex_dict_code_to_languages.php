<?php

use Phinx\Migration\AbstractMigration;

class AddYandexDictCodeToLanguages extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('languages');

        $table
            ->addColumn('yandex_dict_code', 'string', ['limit' => 50, 'null' => true])
            ->save();
        
        $this->getQueryBuilder()
            ->update('languages')
            ->set('yandex_dict_code', 'ru-ru')
            ->where(['id' => 1])
            ->execute();
    }

    public function down()
    {
        $table = $this->table('languages');

        $table
            ->removeColumn('yandex_dict_code')
            ->save();
    }
}
