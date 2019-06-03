<?php

use Phinx\Migration\AbstractMigration;

class InitMenus extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('menus');

        $table
            ->addColumn('link', 'string', ['limit' => 100])
            ->addColumn('text', 'string', ['limit' => 100])
            ->addColumn('position', 'integer')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
