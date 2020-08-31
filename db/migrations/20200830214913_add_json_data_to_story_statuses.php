<?php

use Phinx\Migration\AbstractMigration;

class AddJsonDataToStoryStatuses extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('story_statuses');

        $table
            ->addColumn('json_data', 'text', ['null' => true])
            ->save();
    }

    public function down()
    {
        $table = $this->table('story_statuses');

        $table
            ->removeColumn('json_data')
            ->save();
    }
}
