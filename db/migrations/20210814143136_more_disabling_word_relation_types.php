<?php

use Phinx\Migration\AbstractMigration;

class MoreDisablingWordRelationTypes extends AbstractMigration
{
    public function up()
    {
        $this->execute('update word_relation_types set disabling = 1 where tag in (\'HPH\', \'PHT\')');
    }

    public function down()
    {
        $this->execute('update word_relation_types set disabling = 0 where tag in (\'HPH\', \'PHT\')');
    }
}
