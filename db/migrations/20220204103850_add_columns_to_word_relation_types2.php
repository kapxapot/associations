<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToWordRelationTypes2 extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('word_relation_types');

        $table
            ->addColumn('word_form', 'boolean', ['default' => false])
            ->addColumn('secondary', 'boolean', ['default' => false])
            ->addColumn('weak', 'boolean', ['default' => false])
            ->save();

        $this->execute('update word_relation_types set word_form = 1 where tag in (\'PLU\', \'GRA\', \'PPH\')');

        $this->execute('update word_relation_types set secondary = 1 where tag in (\'ANT\', \'SYN\', \'ERR\', \'GEN\')');

        $this->execute('update word_relation_types set weak = 1 where tag in (\'ANT\', \'SYN\', \'ERR\', \'HPH\', \'PHT\', \'TYP\')');
    }

    public function down()
    {
        $table = $this->table('word_relation_types');

        $table
            ->removeColumn('weak')
            ->removeColumn('secondary')
            ->removeColumn('word_form')
            ->save();
    }
}
