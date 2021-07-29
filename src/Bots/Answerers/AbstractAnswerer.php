<?php

namespace App\Bots\Answerers;

use App\Bots\AbstractBotRequest;
use App\Bots\BotResponse;
use App\Bots\Command;
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

    protected const MESSAGE_CLUELESS = 'Извини{att:те}, не понял{|а}.';
    protected const MESSAGE_WELCOME = '{hello}! Поиграем в ассоциации?';
    protected const MESSAGE_WELCOME_BACK = 'С возвращением!';

    protected const CHUNK_RULES = 'Чтобы узнать, как играть, скажи{att:те} {cmd:rules}.';
    protected const CHUNK_COMMANDS = 'Чтобы узнать, как управлять игрой, скажи{att:те} {cmd:commands}.';
    protected const CHUNK_PLAY = 'Чтобы перейти к игре, скажи{att:те} {cmd:play}.';

    protected const MESSAGE_DEMO = 'Игра идёт в демо-режиме. Для полной игры, пожалуйста, авторизуй{att:тесь|ся}.';

    private const MESSAGE_RULES_COMMON = 'В игре в ассоциации мы с {att:вами|тобой} говорим по очереди слово, которое ассоциируется с предыдущим. Например, я говорю {q:лес}, {att:вы|ты} отвечае{att:те|шь} {q:заяц}, я говорю {q:морковка} и так далее.';

    protected const MESSAGE_RULES_APPLICATION = self::MESSAGE_RULES_COMMON;

    protected const MESSAGE_RULES_USER = self::MESSAGE_RULES_COMMON;

    protected const MESSAGE_COMMANDS_APPLICATION = 'Для пропуска слова скажи{att:те} {cmd:skip} или {cmd:skip_2}. Для выхода скажи{att:те} {cmd:exit}.';

    protected const MESSAGE_COMMANDS_USER = 'Для пропуска слова скажи{att:те} {cmd:skip} или {cmd:skip_2}. Для повтора слова скажи{att:те} {cmd:repeat}. Спроси{att:те} {cmd:what} или {cmd:what_2}, чтобы узнать значение слова. Если {att:вам|тебе} не нравится слово или ассоциация, скажи{att:те} {cmd:word_dislike} или {cmd:association_dislike}. Для выхода скажи{att:те} {cmd:exit}.';

    protected const MESSAGE_SKIP = 'Хорошо.';
    protected const MESSAGE_START_ANEW = 'Начинаем заново:';
    protected const MESSAGE_ERROR = 'Что-то пошло не так.';
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
            : 'У меня нет слов.';
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
            ->withActions(
                Command::RULES,
                Command::COMMANDS,
                Command::PLAY
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
            ['правила'],
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
            'команда',
            'команды'
        );
    }

    protected function isPlayCommand(AbstractBotRequest $request): bool
    {
        return $request->hasAny(
            'игра',
            'играем',
            'играть',
            'играю'
        );
    }

    protected function isNativeBotCommand(AbstractBotRequest $request): bool
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
            'скучно',
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
            'что',
            'кто',
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
            'помощь',
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
            '{att:В|Т}ы вгоняе{att:те|шь} меня в краску.',
            'Ну у {att:вас|тебя} и словечки!',
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
