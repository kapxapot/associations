<?php

use Brightwood\Models\Stories\Core\JsonFileStory;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\WoodStory;
use Phinx\Migration\AbstractMigration;

class InitStories extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('stories');

        $table
            ->addColumn('uuid', 'string', ['null' => true, 'limit' => 36])
            ->addColumn('title', 'string', ['null' => true, 'limit' => Story::MAX_TITLE_LENGTH])
            ->addColumn('description', 'string', ['null' => true, 'limit' => Story::MAX_DESCRIPTION_LENGTH])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $mysteryStory = new JsonFileStory(2, 'mystery.json');
        $kolobokStory = new JsonFileStory(6, '359e097f-5620-477b-930d-48496393f747.json');

        $table
            ->insert([
                [
                    'id' => WoodStory::ID,
                    'title' => WoodStory::TITLE,
                    'description' => WoodStory::DESCRIPTION,
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'id' => $mysteryStory->id(),
                    'title' => $mysteryStory->title(),
                    'description' => $mysteryStory->description(),
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'id' => EightsStory::ID,
                    'title' => EightsStory::TITLE,
                    'description' => EightsStory::DESCRIPTION,
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'id' => $kolobokStory->id(),
                    'title' => $kolobokStory->title(),
                    'description' => $kolobokStory->description(),
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this->table('stories')->drop()->save();
    }
}
