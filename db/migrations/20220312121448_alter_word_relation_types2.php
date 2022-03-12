<?php

use Phinx\Migration\AbstractMigration;

class AlterWordRelationTypes2 extends AbstractMigration
{
    public function up()
    {
        $this->execute('update word_relation_types set sharing_associations_down = 1 where tag in (\'ACR\')');
    }

    public function down()
    {
        $this->execute('update word_relation_types set sharing_associations_down = 0 where tag in (\'ACR\')');
    }
}
