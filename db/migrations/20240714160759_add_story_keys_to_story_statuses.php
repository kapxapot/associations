<?php

use Phinx\Migration\AbstractMigration;

class AddStoryKeysToStoryStatuses extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('story_statuses');

        $table
            ->addColumn('story_version_id', 'integer', ['null' => true])
            ->addForeignKey('story_version_id', 'story_versions', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('story_id', 'stories', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->save();

        $storyIds = [2, 6];

        foreach ($storyIds as $storyId) {
            $this->execute('update story_statuses set story_version_id = (select id from story_versions where story_id = ' . $storyId . ') where story_id = ' . $storyId);
        }
    }
}
