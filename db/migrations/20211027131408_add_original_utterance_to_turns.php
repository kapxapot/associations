<?php

use Phinx\Migration\AbstractMigration;

class AddOriginalUtteranceToTurns extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('turns');

        $table
            ->addColumn('original_utterance', 'string', ['limit' => 250, 'null' => true])
            ->save();
    }
}
