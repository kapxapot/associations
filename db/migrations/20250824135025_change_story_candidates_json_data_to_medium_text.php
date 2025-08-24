<?php

use Phinx\Migration\AbstractMigration;

class ChangeStoryCandidatesJsonDataToMediumText extends AbstractMigration
{
    public function up()
    {
        $this->execute('ALTER TABLE story_candidates MODIFY json_data MEDIUMTEXT');
    }

    public function down()
    {
        $this->execute('ALTER TABLE story_candidates MODIFY json_data TEXT');
    }
}
