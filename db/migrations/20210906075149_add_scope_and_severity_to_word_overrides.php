<?php

use App\Semantics\Scope;
use App\Semantics\Severity;
use Phinx\Migration\AbstractMigration;

class AddScopeAndSeverityToWordOverrides extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('word_overrides');

        $table
            ->addColumn('scope', 'integer', ['null' => true])
            ->addColumn('severity', 'integer', ['null' => true])
            ->save();

        $this->execute('update word_overrides set scope = ' . Scope::DISABLED . ' where disabled = 1');
        $this->execute('update word_overrides set scope = ' . Scope::PUBLIC . ' where disabled = 0 and approved = 1');
        $this->execute('update word_overrides set scope = ' . Scope::PRIVATE . ' where disabled = 0 and approved = 0');

        $this->execute('update word_overrides set severity = ' . Severity::MATURE . ' where mature = 1');
    }

    public function down()
    {
        $table = $this->table('word_overrides');

        $table
            ->removeColumn('severity')
            ->removeColumn('scope')
            ->save();
    }
}
