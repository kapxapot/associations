<?php

use Phinx\Migration\AbstractMigration;

class InitTurns extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('turns');

        $table
            ->addColumn('language_id', 'integer')
            ->addColumn('game_id', 'integer')
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('word_id', 'integer')
            ->addColumn('association_id', 'integer', ['null' => true])
            ->addColumn('prev_turn_id', 'integer', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('finished_at', 'timestamp', ['null' => true])
            ->addForeignKey('language_id', 'languages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('game_id', 'games', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('word_id', 'words', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('association_id', 'associations', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('prev_turn_id', 'turns', 'id', ['delete' => 'SET NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
