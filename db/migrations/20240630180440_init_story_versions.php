<?php

use Brightwood\Models\Stories\Core\JsonFileStory;
use Phinx\Migration\AbstractMigration;

class InitStoryVersions extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('story_versions');

        $table
            ->addColumn('story_id', 'integer')
            ->addColumn('prev_version_id', 'integer', ['null' => true])
            ->addColumn('json_data', 'text')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addForeignKey('story_id', 'stories', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('prev_version_id', 'story_versions', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $mysteryStory = new JsonFileStory(2, 'mystery.json');
        $kolobokStory = new JsonFileStory(6, '359e097f-5620-477b-930d-48496393f747.json');

        $table
            ->insert([
                [
                    'story_id' => $mysteryStory->id(),
                    'json_data' => json_encode($mysteryStory->jsonData()),
                    'created_by' => 1,
                ],
                [
                    'story_id' => $kolobokStory->id(),
                    'json_data' => json_encode($kolobokStory->jsonData()),
                    'created_by' => 1,
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this->table('story_versions')->drop()->save();
    }
}
