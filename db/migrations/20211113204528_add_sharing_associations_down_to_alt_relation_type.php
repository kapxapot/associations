<?php

use Phinx\Migration\AbstractMigration;

class AddSharingAssociationsDownToAltRelationType extends AbstractMigration
{
    public function up()
    {
        $this->execute('update word_relation_types set sharing_associations_down = 1 where tag in (\'ALT\')');
    }

    public function down()
    {
        $this->execute('update word_relation_types set sharing_associations_down = 0 where tag in (\'ALT\')');
    }
}
