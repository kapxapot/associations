<?php

use Phinx\Migration\AbstractMigration;

class InitGenders extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('genders');

        $table
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('tag', 'string', ['limit' => 20])
            ->create();

        $table
            ->insert(
                [
                    [
                        'id' => 1,
                        'name' => 'Мужской',
                        'tag' => 'MAS',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Женский',
                        'tag' => 'FEM',
                    ],
                    [
                        'id' => 3,
                        'name' => 'Средний',
                        'tag' => 'NEU',
                    ],
                    [
                        'id' => 4,
                        'name' => 'Множественный',
                        'tag' => 'PLU',
                    ],
                ]
            )
            ->save();
    }

    public function down()
    {
        $this->table('genders')->drop()->save();
    }
}
