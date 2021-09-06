<?php

use App\Semantics\Scope;
use App\Semantics\Severity;
use Phinx\Migration\AbstractMigration;

class AddScopeAndSeverityToAssociationOverrides extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('association_overrides');

        $table
            ->addColumn('scope', 'integer', ['null' => true])
            ->addColumn('severity', 'integer', ['null' => true])
            ->save();

        $this->execute('update association_overrides set scope = ' . Scope::DISABLED . ' where disabled = 1');
        $this->execute('update association_overrides set scope = ' . Scope::PUBLIC . ' where disabled = 0 and approved = 1');
        $this->execute('update association_overrides set scope = ' . Scope::PRIVATE . ' where disabled = 0 and approved = 0');

        $this->execute('update association_overrides set severity = ' . Severity::MATURE . ' where mature = 1');
    }

    public function down()
    {
        $table = $this->table('association_overrides');

        $table
            ->removeColumn('severity')
            ->removeColumn('scope')
            ->save();
    }
}
