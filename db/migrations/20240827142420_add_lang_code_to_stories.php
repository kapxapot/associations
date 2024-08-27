<?php

use App\Models\Language;
use Phinx\Migration\AbstractMigration;

class AddLangCodeToStories extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('stories');

        $table
            ->addColumn('lang_code', 'string', ['null' => true, 'limit' => 10, 'after' => 'id'])
            ->save();

        $this->execute('update stories set lang_code = \'' . Language::RU . '\' where id IN (1, 2, 3, 7)');

        $this->execute('update stories set lang_code = \'' . Language::EN . '\' where id = 6');
    }
}
