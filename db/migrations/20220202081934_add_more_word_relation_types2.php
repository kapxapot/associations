<?php

use Phinx\Migration\AbstractMigration;

class AddMoreWordRelationTypes2 extends AbstractMigration
{
    public function up()
    {
        $this->execute('update word_relation_types set sharing_associations_down = 1, scope_override = 1 where tag in (\'GRA\')');

        $this->execute('update word_relation_types set sharing_associations_down = 1 where tag in (\'DIM\')');

        $this
            ->table('word_relation_types')
            ->insert([
                [
                    'id' => 15,
                    'name' => 'Grammatical normal form',
                    'tag' => 'GRN',
                    'sharing_pos_down' => 1,
                    'sharing_associations_down' => 1,
                ],
                [
                    'id' => 16,
                    'name' => 'Synonim',
                    'tag' => 'SYN',
                ],
                [
                    'id' => 17,
                    'name' => 'Antonym',
                    'tag' => 'ANT',
                ],
                [
                    'id' => 18,
                    'name' => 'Specific meaning',
                    'tag' => 'SPE',
                    'sharing_pos_down' => 1,
                    'sharing_associations_down' => 1,
                ],
                [
                    'id' => 19,
                    'name' => 'Negation',
                    'tag' => 'NEG',
                    'sharing_pos_down' => 1,
                ],
                [
                    'id' => 20,
                    'name' => 'Error',
                    'tag' => 'ERR',
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this
            ->getQueryBuilder()
            ->delete('word_relation_types')
            ->whereInList('tag', ['GRN', 'SYN', 'ANT', 'SPE', 'NEG', 'ERR'])
            ->execute();

        $this->execute('update word_relation_types set sharing_associations_down = 0 where tag in (\'DIM\')');

        $this->execute('update word_relation_types set sharing_associations_down = 0, scope_override = 2 where tag in (\'GRA\')');
    }
}
