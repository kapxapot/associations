<?php

namespace Brightwood\Answers\Stages;

use Brightwood\Answers\Action;
use Brightwood\Answers\BotCommand;
use Brightwood\Answers\Messages;
use Brightwood\Answers\Stage;
use Brightwood\Answers\Stages\Core\AbstractStage;
use Brightwood\Models\Language;
use Brightwood\Models\Messages\StoryMessageSequence;

class LanguageStage extends AbstractStage
{
    public function enter(): StoryMessageSequence
    {
        return
            StoryMessageSequence::text(
                '[[Please, select your preferred <b>language</b>]]:'
            )
            ->withActions(Action::EN, Action::RU)
            ->withStage(Stage::LANGUAGE);
    }

    public function process(
        string $text,
        ?StoryMessageSequence $successSequence = null
    ): StoryMessageSequence
    {
        /** @var string|null */
        $langCode = null;

        switch ($text) {
            case Action::EN:
                $langCode = Language::EN;
                break;

            case Action::RU:
                $langCode = Language::RU;
                break;
        }

        if (!$langCode) {
            return Messages::writtenSomethingWrong(
                $this->enter()
            );
        }

        $this->tgUser()->withLangCode($langCode);

        return
            StoryMessageSequence::text(
                '[[Thank you! Your language preference has been saved and will now be taken into account.]] 👌',
                '[[You can change your language at any time using the {language_command} command.]]'
            )
            ->merge($successSequence)
            ->withVar('language_command', BotCommand::LANGUAGE);
    }
}
