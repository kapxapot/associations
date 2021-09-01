<?php

use Phinx\Migration\AbstractMigration;

class RemoveTokenizedWordFromWords extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('words');

        $table
            ->removeColumn('tokenized_word')
            ->save();
    }

    public function down()
    {
        $table = $this->table('words');

        $table
            ->addColumn(
                'tokenized_word',
                'string',
                [
                    'limit' => 250,
                    'null' => true,
                    'collation' => 'utf8_bin',
                ]
            )
            ->save();
    }
}
