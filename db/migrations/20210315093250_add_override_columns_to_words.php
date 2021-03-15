<?php

use Phinx\Migration\AbstractMigration;

class AddOverrideColumnsToWords extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('words');

        $table
            ->addColumn('tokenized_word', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('corrected_word', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('disabled', 'boolean', ['default' => false])
            ->save();
    }
}
