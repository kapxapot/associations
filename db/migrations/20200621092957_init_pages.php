<?php

use Phinx\Migration\AbstractMigration;

class InitPages extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('pages');

        $table
            ->addColumn('parent_id', 'integer', ['null' => true])
            ->addColumn('title', 'string', ['limit' => 250])
            ->addColumn('slug', 'string', ['limit' => 250])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('tags', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('published', 'boolean', ['default' => false])
            ->addColumn('published_at', 'timestamp', ['null' => true])
            ->addColumn('show_in_feed', 'boolean', ['default' => false])
            ->addColumn('skip_in_breadcrumbs', 'boolean', ['default' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addForeignKey('parent_id', 'pages', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('updated_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
