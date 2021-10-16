<?php

use Phinx\Migration\AbstractMigration;

class AddModeWordRelationTypes extends AbstractMigration
{
    public function up()
    {
        $this
            ->table('word_relation_types')
            ->insert([
                [
                    'id' => 13,
                    'name' => 'Acronym',
                    'tag' => 'ACR',
                    'disabling' => 0,
                ],
                [
                    'id' => 14,
                    'name' => 'Augmentative form',
                    'tag' => 'AUG',
                    'disabling' => 0,
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this
            ->getQueryBuilder()
            ->delete('word_relation_types')
            ->whereInList('tag', ['ACR', 'AUG'])
            ->execute();
    }
}
