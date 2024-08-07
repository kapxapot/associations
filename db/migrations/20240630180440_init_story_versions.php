<?php

use Brightwood\JsonDataLoader;
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
            ->addIndex(['story_id', 'prev_version_id'], ['unique' => true])
            ->create();

        $dir = __DIR__ . '/../../brightwood/Models/Stories/Json/';

        $mysteryData = JsonDataLoader::load($dir . 'mystery.json');
        $kolobokData = JsonDataLoader::load($dir . '359e097f-5620-477b-930d-48496393f747.json');

        $table
            ->insert([
                [
                    'story_id' => 2,
                    'json_data' => json_encode($mysteryData),
                ],
                [
                    'story_id' => 6,
                    'json_data' => json_encode($kolobokData),
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this->table('story_versions')->drop()->save();
    }
}
