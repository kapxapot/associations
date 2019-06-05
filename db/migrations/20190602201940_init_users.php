<?php

use Phinx\Migration\AbstractMigration;

class InitUsers extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('users');

        $table
            ->addColumn('login', 'string', ['limit' => 20])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('role_id', 'integer', ['null' => true, 'default' => 3])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $table
            ->insert([
                [
                    'id' => 1,
                    'login' => 'admin',
                    'password' => '$2y$10$ZPbyuHSy/eOgUhXr07fMCeRphu1qsJRRAB5ij9alZWSKM4r0TR1zW', // 'admin'
                    'role_id' => 1,
                ],
            ])
            ->save();
    }

    public function down()
    {
        $this->table('users')->drop()->save();
    }
}
