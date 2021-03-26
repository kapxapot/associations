<?php

use Phinx\Migration\AbstractMigration;

class AddMoreTimestampsToWords extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('words');

        $table
            ->addColumn('disabled_updated_at', 'timestamp', ['null' => true])
            ->addColumn('word_updated_at', 'timestamp', ['null' => true])
            ->save();
    }
}
