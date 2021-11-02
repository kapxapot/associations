<?php

use App\Semantics\Scope;
use Phinx\Migration\AbstractMigration;

class UpdateWordRelationTypes extends AbstractMigration
{
    public function up()
    {
        $this->execute('update word_relation_types set scope_override = ' . Scope::INACTIVE . ' where tag in (\'GRA\', \'TRN\')');

        $this->execute('update word_relation_types set sharing_pos_down = 1 where tag not in (\'TYP\', \'HPH\', \'PHT\', \'PPH\')');
    }

    public function down()
    {
        $this->execute('update word_relation_types set sharing_pos_down = 0');

        $this->execute('update word_relation_types set scope_override = ' . Scope::DISABLED . ' where scope_override = ' . Scope::INACTIVE);
    }
}
