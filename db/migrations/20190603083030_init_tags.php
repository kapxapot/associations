<?php

use Phinx\Migration\AbstractMigration;

class InitTags extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('tags');

        $table
            ->addColumn('entity_type', 'string', ['limit' => 100])
            ->addColumn('entity_id', 'integer')
            ->addColumn('tag', 'string', ['limit' => 250])
            ->create();
    }
}
