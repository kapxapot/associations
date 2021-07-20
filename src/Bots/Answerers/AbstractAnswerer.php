<?php

namespace App\Bots\Answerers;

use App\Bots\AbstractBotRequest;
use App\Bots\BotResponse;
use App\Models\Language;
use App\Models\Word;
use App\Services\LanguageService;
use Plasticode\Collections\Generic\StringCollection;

abstract class AbstractAnswerer
{
    protected const VAR_STATE = 'state';
    protected const VAR_COMMAND = 'command';

    protected const STATE_HELP = 'help';
    protected const STATE_RULES = 'rules';
    protected const STATE_COMMANDS = 'commands';
    protected const STATE_COMMAND_CONFIRM = 'command_confirm';

    protected const COMMAND_HELP = 'помощь';
    protected const COMMAND_RULES = 'правила';
    protected const COMMAND_COMMAND = 'команда';
    protected const COMMAND_COMMANDS = 'команды';
    protected const COMMAND_PLAYING = 'играем';

    protected const MESSAGE_CLUELESS = 'Извините, не поняла';
    protected const MESSAGE_WELCOME = 'Привет! Поиграем в ассоциации?';
    protected const MESSAGE_WELCOME_BACK = 'С возвращением!';

    protected const CHUNK_RULES = 'Чтобы узнать, как играть, скажите \'правила\'.';
    protected const CHUNK_COMMANDS = 'Чтобы узнать, как управлять игрой, скажите \'команды\'.';
    protected const CHUNK_PLAY = 'Чтобы перейти к игре, скажите \'играть\'.';

    protected const MESSAGE_DEMO = 'Игра идет в демо-режиме. Для полной игры, пожалуйста, авторизуйтесь.';

    private const MESSAGE_RULES_COMMON = 'В игре в ассоциации мы с вами говорим по очереди слово, которое ассоциируется с предыдущим. Например, я говорю \'лес\', вы отвечаете \'заяц\', я говорю \'морковка\' и т.д.';

    protected const MESSAGE_RULES_APPLICATION = self::MESSAGE_RULES_COMMON;

    protected const MESSAGE_RULES_USER = self::MESSAGE_RULES_COMMON;

    protected const MESSAGE_COMMANDS_APPLICATION = 'Для пропуска слова скажите \'другое слово\' или \'дальше\'. Для выхода из игры скажите \'хватит\'.';

    protected const MESSAGE_COMMANDS_USER = 'Для пропуска слова скажите \'другое слово\' или \'дальше\'. Для повтора слова скажите \'повтори\'. Спросите \'что?\' или \'что это?\', чтобы узнать значение слова. Если вам не нравится слово или ассоциация, скажите \'плохое слово\' или \'плохая ассоциация\'. Для выхода скажите \'хватит\'.';

    protected const MESSAGE_SKIP = 'Хорошо.';
    protected const MESSAGE_START_ANEW = 'Начинаем заново:';
    protected const MESSAGE_ERROR = 'Что-то пошло не так';
    protected const MESSAGE_CONTINUE = 'Продолжаем. Мое слово:';

    protected LanguageService $languageService;

    public function __construct(
        LanguageService $languageService
    )
    {
        $this->languageService = $languageService;
    }

    protected function getLanguage(): Language
    {
        return $this->languageService->getDefaultLanguage();
    }

    protected function renderWord(?Word $word): string
    {
        return $word !== null
            ? mb_strtoupper($word->word)
            : 'У меня нет слов';
    }

    protected function cluelessResponse(): BotResponse
    {
        return $this->buildResponse(self::MESSAGE_CLUELESS);
    }

    public function helpCommand(
        AbstractBotRequest $request,
        string ...$prependMessages
    ): BotResponse
    {
        return $this
            ->buildResponse(
                $prependMessages,
                self::CHUNK_RULES,
                self::CHUNK_COMMANDS,
                self::CHUNK_PLAY
            )
            ->withVarBy($request, self::VAR_STATE, self::STATE_HELP);
    }

    /**
     * @param array<string[]|string> ...$parts
     */
    protected function buildResponse(...$parts): BotResponse
    {
        $lines = [];

        foreach ($parts as $part) {
            if (is_array($part)) {
                $lines = array_merge($lines, $part);
            } else {
                $lines[] = $part;
            }
        }

        return new BotResponse(...$lines);
    }

    protected function isHelpDialog(AbstractBotRequest $request): bool
    {
        $state = $request->var(self::VAR_STATE);

        $helpStates = [
            self::STATE_HELP,
            self::STATE_RULES,
            self::STATE_COMMANDS,
        ];

        return in_array($state, $helpStates);
    }

    protected function isHelpRulesCommand(AbstractBotRequest $request): bool
    {
        return $request->hasAnySet(
            [self::COMMAND_RULES],
            ['правила', 'игры'],
            ['смысл', 'игры'],
            ['цель', 'игры'],
            ['как', 'играть'],
            ['как', 'использовать'],
            ['как', 'пользоваться']
        );
    }

    protected function isHelpCommandsCommand(AbstractBotRequest $request): bool
    {
        return $request->hasAny(
            self::COMMAND_COMMANDS,
            'команда'
        );
    }

    protected function isPlayCommand(AbstractBotRequest $request): bool
    {
        return $request->hasAny(
            'играть',
            'игра',
            'играю',
            self::COMMAND_PLAYING
        );
    }

    protected function isNativeCommand(AbstractBotRequest $request): bool
    {
        return $request->hasAny(
            'включи',
            'включить',
            'выйди',
            'выйти',
            'выключай',
            'выключайся',
            'выключи',
            'выключись',
            'выключить',
            'выходить',
            'домой',
            'заканчиваем',
            'закончили',
            'закончим',
            'закончить',
            'закройся',
            'запусти',
            'запустить',
            'заткнись',
            'играй',
            'надоело',
            'останови',
            'остановись',
            'отключи',
            'отключись',
            'отключить',
            'открой',
            'открыть',
            'отстань',
            'поиграем',
            'пока',
            'покажи',
            'прекрати',
            'прекратить',
            'прекращай',
            'прекращаем',
            'скажи',
            'устал',
            'устала',
            'хватит'
        ) || $request->hasAnySet(
            ['стоп', 'игра'],
            ['другая', 'игра'],
            ['конец', 'игры'],
            ['на', 'главную'],
            ['пауза', 'игры']
        );
    }

    protected function isWhatCommand(AbstractBotRequest $request): bool
    {
        return $request->hasAny(
            'кто',
            'что',
            'чего',
            'непонятная',
            'непонятное',
            'непонятные',
            'объясни'
        ) || $request->hasAnySet(
            ['не', 'понял'],
            ['не', 'поняла'],
            ['не', 'понятно'],
            ['значение', 'слова'],
            ['значит', 'слово'],
            ['обозначение', 'слова']
        );
    }

    protected function isRepeatCommand(AbstractBotRequest $request): bool
    {
        return $request->hasAny(
            'играть',
            'играем',
            'повтори',
            'повторите',
            'повторить',
            'подожди',
            'продолжим',
            'продолжаем'
        ) || $request->hasAnySet(
            ['еще', 'раз'],
            ['какое', 'слово'],
            ['не', 'расслышал'],
            ['не', 'расслышала'],
            ['не', 'слышно']
        );
    }

    protected function isHelpCommand(AbstractBotRequest $request): bool
    {
        return $request->isAny(
            self::COMMAND_HELP,
            'что ты умеешь'
        );
    }

    protected function isSkipCommand(AbstractBotRequest $request): bool
    {
        return $request->hasAny(
            'было',
            'далее',
            'дальше',
            'другую',
            'заново',
            'начинаем',
            'начинай',
            'начни',
            'продолжаем',
            'продолжай',
            'продолжать',
            'продолжи',
            'продолжим',
            'продолжить',
            'пропускаем',
            'пропускай',
            'пропускаю',
            'пропусти',
            'пропустить',
            'сдаюсь',
            'следующая',
            'следующее',
            'следующий',
            'следующую',
            'сначала'
        ) || $request->hasAnySet(
            ['нет', 'слов'],
            ['нет', 'ассоциации'],
            ['нет', 'ассоциаций'],
            ['нечего', 'сказать'],
            ['не', 'знаю'],
            ['в', 'тупике'],
            ['можно', 'другое'],
            ['давай', 'другое'],
            ['другое', 'слово'],
            ['другая', 'ассоциация'],
            ['другую', 'ассоциацию'],
            ['можно', 'другую'],
            ['давай', 'другую'],
            ['новая', 'игра'],
            ['новую', 'игру'],
            ['начать', 'игру']
        );
    }

    protected function matureWordMessage(): string
    {
        return $this->randomString(
            'Ой! Надеюсь, рядом нет детей.',
            'Вы вгоняете меня в краску.',
            'Ну у вас и словечки!',
            'Хм... Как скажете.'
        );
    }

    protected function noAssociationMessage(): string
    {
        return $this->randomString(
            'У меня нет ассоциаций.',
            'Мне нечего сказать.',
            'Я в тупике.',
            'Я сдаюсь.'
        );
    }

    protected function randomString(string ...$strings): ?string
    {
        return StringCollection::make($strings)->random();
    }

    protected function findWord(?string $wordStr): ?Word
    {
        $language = $this->getLanguage();

        return $this->languageService->findWord($language, $wordStr);
    }

    /**
     * @return string[]
     */
    protected function getKnownVars(): array
    {
        return [
            self::VAR_STATE,
            self::VAR_COMMAND,
        ];
    }
}
