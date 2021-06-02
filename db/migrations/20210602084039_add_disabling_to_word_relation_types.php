<?php

use Phinx\Migration\AbstractMigration;

class AddDisablingToWordRelationTypes extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('word_relation_types');

        $table
            ->addColumn('disabling', 'boolean', ['default' => false])
            ->save();

        $this->execute('update word_relation_types set disabling = 1 where tag in (\'TYP\', \'GRA\')');
    }

    public function down()
    {
        $table = $this->table('word_relation_types');

        $table
            ->removeColumn('disabling')
            ->save();
    }
}
