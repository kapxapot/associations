<?php

namespace Brightwood\Answers\Stages;

use Brightwood\Answers\Action;
use Brightwood\Answers\BotCommand;
use Brightwood\Answers\Messages;
use Brightwood\Answers\Stage;
use Brightwood\Answers\Stages\Core\AbstractStage;
use Brightwood\Models\Messages\StoryMessageSequence;
use Plasticode\Semantics\Gender;

class GenderStage extends AbstractStage
{
    public function enter(): StoryMessageSequence
    {
        return
            StoryMessageSequence::text(
                '[[For better story texts, please provide your <b>gender</b>]]:'
            )
            ->withActions(Action::MAS, Action::FEM)
            ->withStage(Stage::GENDER);
    }

    public function process(
        string $text,
        ?StoryMessageSequence $successSequence = null
    ): StoryMessageSequence
    {
        /** @var integer|null */
        $gender = null;

        // actions must be translated to be checked correctly
        switch ($text) {
            case $this->parse(Action::MAS):
                $gender = Gender::MAS;
                break;

            case $this->parse(Action::FEM):
                $gender = Gender::FEM;
                break;
        }

        if (!$gender) {
            return Messages::writtenSomethingWrong(
                $this->enter()
            );
        }

        $this->tgUser()->withGenderId($gender);

        return
            StoryMessageSequence::text(
                '[[Thank you, dear {{👦|👧}}, your gender has been saved and will now be taken into account.]] 👌',
                '[[You can change your gender at any time using the {{gender_command}} command.]]'
            )
            ->merge($successSequence)
            ->withVar('gender_command', BotCommand::GENDER);
    }
}
