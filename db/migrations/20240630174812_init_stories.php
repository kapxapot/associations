<?php

use Brightwood\Models\Stories\Core\JsonFileStory;
use Brightwood\Models\Stories\EightsStory;
use Brightwood\Models\Stories\WoodStory;
use Phinx\Migration\AbstractMigration;

class InitStories extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('stories');

        $table
            ->addColumn('uuid', 'uuid', ['null' => true])
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
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'id' => $mysteryStory->id(),
                    'uuid' => $mysteryStory->uuid(),
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'id' => EightsStory::ID,
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'id' => $kolobokStory->id(),
                    'uuid' => $kolobokStory->uuid(),
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
