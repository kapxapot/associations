<?php

use Phinx\Migration\AbstractMigration;

class InitYandexDictWords extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('yandex_dict_words');

        $table
            ->addColumn('word', 'string', ['limit' => 250])
            ->addColumn('word_id', 'integer', ['null' => true])
            ->addColumn('language_id', 'integer', ['null' => true])
            ->addColumn('response', 'text', ['null' => true])
            ->addColumn('pos', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('word_id', 'words', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('language_id', 'languages', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
