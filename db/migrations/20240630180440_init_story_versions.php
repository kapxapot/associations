<?php

use Phinx\Migration\AbstractMigration;

class InitStoryVersions extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('story_versions');

        $table
            ->addColumn('story_id', 'integer')
            ->addColumn('prev_version_id', 'integer', ['null' => true])
            ->addColumn('uuid', 'string', ['limit' => 36])
            ->addColumn('title', 'string', ['limit' => 250])
            ->addColumn('description', 'string', ['null' => true, 'limit' => 1000])
            ->addColumn('json', 'text')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addForeignKey('story_id', 'stories', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('prev_version_id', 'story_versions', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
