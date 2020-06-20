<?php

use Phinx\Migration\AbstractMigration;

class AddCodeToLanguages extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('languages');

        $table
            ->addColumn('code', 'string', ['limit' => 10, 'null' => true])
            ->save();
        
        $this->getQueryBuilder()
            ->update('languages')
            ->set('code', 'ru')
            ->where(['id' => 1])
            ->execute();
    }

    public function down()
    {
        $table = $this->table('languages');

        $table
            ->removeColumn('code')
            ->save();
    }
}
