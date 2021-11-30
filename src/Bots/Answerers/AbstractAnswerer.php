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
    protected const MESSAGE_BYE = '{att:До свидания|Пока}! Заходи{att:те} ещё!';

    protected const CHUNK_RULES = 'Чтобы узнать, как играть, скажи{att:те} {cmd:rules}.';
    protected const CHUNK_COMMANDS = 'Чтобы узнать, как управлять игрой, скажи{att:те} {cmd:commands}.';
    protected const CHUNK_PLAY = 'Чтобы перейти к игре, скажи{att:те} {cmd:play}.';

    protected const MESSAGE_DEMO = 'Игра идёт в демо-режиме. Для полной игры, пожалуйста, авторизуй{att:тесь|ся}.';

    private const MESSAGE_RULES = 'В игре в ассоциации мы с {att:вами|тобой} говорим по очереди слово, которое ассоциируется с предыдущим. Например, я говорю {q:лес}, {att:вы|ты} отвечае{att:те|шь} {q:заяц}, я говорю {q:морковка} и так далее.';

    protected const MESSAGE_COMMANDS_APPLICATION = 'Для пропуска слова скажи{att:те} {cmd:skip}. Для выхода скажи{att:те} {cmd:enough}.';

    protected const MESSAGE_COMMANDS_USER = 'Для пропуска слова скажи{att:те} {cmd:skip}. Для повтора слова скажи{att:те} {cmd:repeat}. Спроси{att:те} {cmd:what}, чтобы узнать значение слова. Если {att:вам|тебе} не нравится слово или ассоциация, скажи{att:те} {cmd:word_dislike} или {cmd:association_dislike}. Для выхода скажи{att:те} {cmd:enough}.';

    protected const MESSAGE_SKIP = 'Хорошо.';
    protected const MESSAGE_START_ANEW = 'Новое слово:';
    protected const MESSAGE_ERROR = 'Что-то пошло не так.';
    protected const MESSAGE_CONTINUE = 'Продолжаем. Моё слово:';

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

    protected function helpDialog(
        AbstractBotRequest $request,
        callable $playCommandHandler
    ): BotResponse
    {
        if ($this->isHelpRulesCommand($request)) {
            return $this->rulesCommand($request);
        }

        if ($this->isHelpCommandsCommand($request)) {
            return $this->commandsCommand($request);
        }

        if ($this->isPlayCommand($request)) {
            return ($playCommandHandler)();
        }

        return $this->helpCommand($request, self::MESSAGE_CLUELESS);
    }

    protected function helpCommand(
        AbstractBotRequest $request,
        string ...$prependMessages
    ): BotResponse
    {
        return $this
            ->shortHelpCommand($request, ...$prependMessages)
            ->addLines(
                self::CHUNK_RULES,
                self::CHUNK_COMMANDS,
                self::CHUNK_PLAY
            );
    }

    protected function shortHelpCommand(
        AbstractBotRequest $request,
        string ...$messages
    ): BotResponse
    {
        return $this
            ->buildResponse($messages)
            ->withActions(
                Command::RULES,
                Command::COMMANDS,
                Command::PLAY
            )
            ->withVarBy($request, self::VAR_STATE, self::STATE_HELP);
    }

    protected function rulesCommand(AbstractBotRequest $request): BotResponse
    {
        return $this
            ->buildResponse(
                $this->getRulesMessage(),
                self::CHUNK_COMMANDS,
                self::CHUNK_PLAY
            )
            ->withActions(
                Command::COMMANDS,
                Command::PLAY
            )
            ->withVarBy($request, self::VAR_STATE, self::STATE_RULES);
    }

    protected function commandsCommand(AbstractBotRequest $request): BotResponse
    {
        return $this
            ->buildResponse(
                $this->getCommandsMessage(),
                self::CHUNK_RULES,
                self::CHUNK_PLAY
            )
            ->withActions(
                Command::RULES,
                Command::PLAY
            )
            ->withVarBy($request, self::VAR_STATE, self::STATE_COMMANDS);
    }

    protected function exitCommand(): BotResponse
    {
        return $this
            ->buildResponse(self::MESSAGE_BYE)
            ->withEndSession(true);
    }

    protected function getRulesMessage(): string
    {
        return self::MESSAGE_RULES;
    }

    abstract protected function getCommandsMessage(): string;

    /**
     * @param array<string[]|string> $parts
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
            'включите',
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
            'завершить',
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
            'наигралась',
            'наигрался',
            'останови',
            'остановись',
            'отвали',
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
            'сыграем',
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
            ['не', 'поняли'],
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
            'помолчи',
            'помолчите',
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
            'пропустим',
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
            ['нету', 'слов'],
            ['нету', 'ассоциации'],
            ['нету', 'ассоциаций'],
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
            ['начать', 'игру'],
            ['новая', 'слово'],
            ['новое', 'слово'],
            ['новые', 'слово'],
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
