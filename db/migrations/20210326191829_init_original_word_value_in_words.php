<?php

use Phinx\Migration\AbstractMigration;

class InitOriginalWordValueInWords extends AbstractMigration
{
    public function up()
    {
        $this->execute('update words set original_word = word_bin');
    }

    public function down()
    {
        $this->execute('update words set original_word = null');
    }
}
