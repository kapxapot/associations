<?php

use Phinx\Migration\AbstractMigration;

class AddWordRelationTypes extends AbstractMigration
{
    public function up()
    {
        $this
            ->table('word_relation_types')
            ->insert([
                [
                    'id' => 7,
                    'name' => 'Homophone',
                    'tag' => 'HPH',
                ],
                [
                    'id' => 8,
                    'name' => 'Phonetic typo',
                    'tag' => 'PHT',
                ],
                [
                    'id' => 9,
                    'name' => 'Translation',
                    'tag' => 'TRN',
                ],
                [
                    'id' => 10,
                    'name' => 'Localization',
                    'tag' => 'LOC',
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this
            ->getQueryBuilder()
            ->delete('word_relation_types')
            ->whereInList('tag', ['HPH', 'PHT', 'TRN', 'LOC'])
            ->execute();
    }
}
