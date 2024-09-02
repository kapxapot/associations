<?php

use Phinx\Migration\AbstractMigration;

class AddDeletedToStories extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('stories');

        $table
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->save();
    }
}
