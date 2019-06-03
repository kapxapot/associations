<?php

use Phinx\Migration\AbstractMigration;

class InitMenuItems extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('menu_items');

        $table
            ->addColumn('menu_id', 'integer')
            ->addColumn('link', 'string', ['limit' => 100])
            ->addColumn('text', 'string', ['limit' => 100])
            ->addColumn('position', 'integer')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('menu_id', 'menus', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
