<?php

use Phinx\Migration\AbstractMigration;

class InitStoryStatuses extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('story_statuses');

        $table
            ->addColumn('telegram_user_id', 'integer')
            ->addColumn('story_id', 'integer')
            ->addColumn('step_id', 'integer')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('telegram_user_id', 'telegram_users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
