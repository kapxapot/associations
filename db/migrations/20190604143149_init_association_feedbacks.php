<?php

use Phinx\Migration\AbstractMigration;

class InitAssociationFeedbacks extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('association_feedbacks');

        $table
            ->addColumn('association_id', 'integer')
            ->addColumn('dislike', 'boolean', ['default' => false])
            ->addColumn('mature', 'boolean', ['default' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer')
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('association_id', 'associations', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
