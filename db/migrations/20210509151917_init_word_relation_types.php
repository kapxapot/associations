<?php

use Phinx\Migration\AbstractMigration;

class InitWordRelationTypes extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('word_relation_types');

        $table
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('tag', 'string', ['limit' => 20])
            ->create();

        $table
            ->insert([
                [
                    'id' => 1,
                    'name' => 'Plural form',
                    'tag' => 'PLU',
                ],
                [
                    'id' => 2,
                    'name' => 'Typo',
                    'tag' => 'TYP',
                ],
                [
                    'id' => 3,
                    'name' => 'Alternative form',
                    'tag' => 'ALT',
                ],
                [
                    'id' => 4,
                    'name' => 'Grammatical form',
                    'tag' => 'GRA',
                ],
                [
                    'id' => 5,
                    'name' => 'Diminutive form',
                    'tag' => 'DIM',
                ],
                [
                    'id' => 6,
                    'name' => 'Gender form',
                    'tag' => 'GEN',
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this->table('word_relation_types')->drop()->save();
    }
}
