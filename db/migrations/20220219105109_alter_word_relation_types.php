<?php

use Phinx\Migration\AbstractMigration;

class AlterWordRelationTypes extends AbstractMigration
{
    public function up()
    {
        $this->execute('update word_relation_types set secondary = 1, weak = 1 where tag in (\'NEG\')');

        $this->execute('update word_relation_types set name = \'Cognate word\', tag = \'COG\', sharing_pos_down = 0, sharing_associations_down = 0, secondary = 1 where tag in (\'GRN\')');
    }

    public function down()
    {
        $this->execute('update word_relation_types set name = \'Grammatical normal form\', tag = \'GRN\', sharing_pos_down = 1, sharing_associations_down = 1, secondary = 0 where tag in (\'COG\')');

        $this->execute('update word_relation_types set secondary = 0, weak = 0 where tag in (\'NEG\')');
    }
}
