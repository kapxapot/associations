<?php

use Phinx\Migration\AbstractMigration;

class ChangeWordOverrides extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('word_overrides');

        $table
            ->changeColumn('disabled', 'boolean', ['default' => false])
            ->save();
    }

    public function down()
    {
        $table = $this->table('word_overrides');

        $table
            ->addColumn('disabled', 'boolean', ['null' => true])
            ->save();
    }
}
