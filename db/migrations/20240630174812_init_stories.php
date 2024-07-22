<?php

use Brightwood\JsonDataLoader;
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
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $dir = __DIR__ . '/../../brightwood/Models/Stories/Json/';

        $mysteryData = JsonDataLoader::load($dir . 'mystery.json');
        $kolobokData = JsonDataLoader::load($dir . '359e097f-5620-477b-930d-48496393f747.json');

        $table
            ->insert([
                [
                    'id' => WoodStory::ID,
                ],
                [
                    'id' => 2,
                    'uuid' => $mysteryData['id'],
                ],
                [
                    'id' => EightsStory::ID,
                ],
                [
                    'id' => 6,
                    'uuid' => $kolobokData['id'],
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this->table('stories')->drop()->save();
    }
}
