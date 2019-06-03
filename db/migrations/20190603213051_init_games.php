<?php

use Phinx\Migration\AbstractMigration;

class InitGames extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('games');

        $table
            ->addColumn('user_id', 'integer')
            ->addColumn('language_id', 'integer')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('finished_at', 'timestamp', ['null' => true])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('language_id', 'languages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
