<?php

use Phinx\Migration\AbstractMigration;

class InitYandexDictWords extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('yandex_dict_words');

        $table
            ->addColumn('word', 'string', ['limit' => 250])
            ->addColumn('language', 'string', ['limit' => 50])
            ->addColumn('response', 'text', ['null' => true])
            ->addColumn('pos', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
