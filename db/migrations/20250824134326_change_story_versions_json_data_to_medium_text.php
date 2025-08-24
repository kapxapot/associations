<?php

use Phinx\Migration\AbstractMigration;

class ChangeStoryVersionsJsonDataToMediumText extends AbstractMigration
{
    public function up()
    {
        $this->execute('ALTER TABLE story_versions MODIFY json_data MEDIUMTEXT');
    }

    public function down()
    {
        $this->execute('ALTER TABLE story_versions MODIFY json_data TEXT');
    }
}
