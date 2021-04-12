<?php

use Phinx\Migration\AbstractMigration;

class InitAssociationOverrides extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('association_overrides');

        $table
            ->addColumn('association_id', 'integer')
            ->addColumn('approved', 'boolean', ['null' => true])
            ->addColumn('mature', 'boolean', ['null' => true])
            ->addColumn('disabled', 'boolean', ['default' => false])
            ->addColumn('created_by', 'integer')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('association_id', 'associations', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
