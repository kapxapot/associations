<?php

use Phinx\Migration\AbstractMigration;

class AddOriginalUtteranceToWords extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('words');

        $table
            ->addColumn('original_utterance', 'string', ['limit' => 250, 'null' => true])
            ->save();
    }
}
