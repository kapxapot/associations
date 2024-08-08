<?php

use Phinx\Migration\AbstractMigration;

class AddUuidToStoryCandidates extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('story_candidates');

        $table
            ->addColumn('uuid', 'uuid', ['after' => 'id'])
            ->save();
    }
}
