<?php

use Phinx\Migration\AbstractMigration;

class AddSourceStoryIdToStories extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('stories');

        $table
            ->addColumn('source_story_id', 'integer', ['null' => true, 'after' => 'uuid'])
            ->addForeignKey('source_story_id', 'stories', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->save();
    }
}
