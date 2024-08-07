<?php

use Phinx\Migration\AbstractMigration;

class InitStoryCandidates extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('story_candidates');

        $table
            ->addColumn('json_data', 'text')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer')
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }

    public function down()
    {
        $this->table('story_candidates')->drop()->save();
    }
}
