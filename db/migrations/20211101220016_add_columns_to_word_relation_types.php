<?php

use App\Semantics\Scope;
use Phinx\Migration\AbstractMigration;

class AddColumnsToWordRelationTypes extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('word_relation_types');

        $table
            ->addColumn('scope_override', 'integer', ['null' => true])
            ->addColumn('sharing_pos_down', 'boolean', ['default' => false])
            ->addColumn('sharing_associations_down', 'boolean', ['default' => false])
            ->save();

        $this->execute('update word_relation_types set scope_override = ' . Scope::DISABLED . ' where disabling = 1');
    }

    public function down()
    {
        $table = $this->table('word_relation_types');

        $table
            ->removeColumn('sharing_associations_down')
            ->removeColumn('sharing_pos_down')
            ->removeColumn('scope_override')
            ->save();
    }
}
