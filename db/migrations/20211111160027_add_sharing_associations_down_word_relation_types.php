<?php

use Phinx\Migration\AbstractMigration;

class AddSharingAssociationsDownWordRelationTypes extends AbstractMigration
{
    public function up()
    {
        $this->execute('update word_relation_types set sharing_associations_down = 1 where tag in (\'LOC\', \'PLU\')');
    }

    public function down()
    {
        $this->execute('update word_relation_types set sharing_associations_down = 0 where tag in (\'LOC\', \'PLU\')');
    }
}
