<?php

use App\Semantics\Scope;
use App\Semantics\Severity;
use Phinx\Migration\AbstractMigration;

class AddScopeAndSeverityToAssociations extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('associations');

        $table
            ->addColumn('scope', 'integer', ['default' => Scope::PRIVATE])
            ->addColumn('scope_updated_at', 'timestamp', ['null' => true])
            ->addColumn('severity', 'integer', ['default' => Severity::NEUTRAL])
            ->addColumn('severity_updated_at', 'timestamp', ['null' => true])
            ->save();

        $this->execute('update associations set scope = ' . Scope::DISABLED . ' where disabled = 1');
        $this->execute('update associations set scope = ' . Scope::PUBLIC . ' where disabled = 0 and approved = 1');

        $this->execute('update associations set severity = ' . Severity::MATURE . ' where mature = 1');

        $this->execute('update associations set scope_updated_at = nullif(greatest(coalesce(disabled_updated_at, 0), coalesce(approved_updated_at, 0)), 0), severity_updated_at = mature_updated_at');
    }

    public function down()
    {
        $table = $this->table('associations');

        $table
            ->removeColumn('severity_updated_at')
            ->removeColumn('severity')
            ->removeColumn('scope_updated_at')
            ->removeColumn('scope')
            ->save();
    }
}
