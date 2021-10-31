<?php

use Phinx\Migration\AbstractMigration;

class UpdatePredicativeToParticle extends AbstractMigration
{
    public function up()
    {
        $this->execute('update word_overrides set pos_correction = REPLACE(pos_correction, \'predicative\', \'particle\') where pos_correction is not null');
    }

    public function down()
    {
        $this->execute('update word_overrides set pos_correction = REPLACE(pos_correction, \'particle\', \'predicative\') where pos_correction is not null');
    }
}
