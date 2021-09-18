<?php

use Phinx\Migration\AbstractMigration;

class AddMoreWordRelationTypes extends AbstractMigration
{
    public function up()
    {
        $this
            ->table('word_relation_types')
            ->insert([
                [
                    'id' => 11,
                    'name' => 'Duplicate',
                    'tag' => 'DUP',
                    'disabling' => 1,
                ],
                [
                    'id' => 12,
                    'name' => 'Prepositional phrase',
                    'tag' => 'PPH',
                    'disabling' => 1,
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this
            ->getQueryBuilder()
            ->delete('word_relation_types')
            ->whereInList('tag', ['DUP', 'PPH'])
            ->execute();
    }
}
