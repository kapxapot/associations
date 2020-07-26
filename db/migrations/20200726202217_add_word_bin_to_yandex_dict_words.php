<?php

use Phinx\Migration\AbstractMigration;

class AddWordBinToYandexDictWords extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('yandex_dict_words');

        $table
            ->addColumn('word_bin', 'string', ['limit' => 250, 'collation' => 'utf8_bin'])
            ->save();

        $this->execute('update yandex_dict_words set word_bin = word');
    }

    public function down()
    {
        $table = $this->table('yandex_dict_words');

        $table
            ->removeColumn('word_bin')
            ->save();
    }
}
