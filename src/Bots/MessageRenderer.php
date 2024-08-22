<?php

namespace App\Bots;

use App\Bots\Interfaces\MessageRendererInterface;
use Brightwood\Translation\Interfaces\TranslatorInterface;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Semantics\Gender;

/**
 * Renders the following constructs:
 *
 * - [text] - translates text using the provided translator
 * - {one|two|three} - based on genders (1, 2, 3)
 * - {var_name} - renders var value
 * - {handler:text} - applies handler to text
 * - {var_name:one|two|three} - based on var value (1, 2, 3)
 */
class MessageRenderer implements MessageRendererInterface
{
    /** @var array<string, mixed> */
    private array $vars;

    /** @var array<string, callable> */
    private array $handlers;

    private ?TranslatorInterface $translator = null;

    private int $gender;

    public function __construct()
    {
        $this->vars = [];
        $this->handlers = [];
        $this->gender = Gender::MAS;
    }

    public function withGender(int $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    public function withVars(array $vars): self
    {
        foreach ($vars as $name => $value) {
            $this->withVar($name, $value);
        }

        return $this;
    }

    public function withVar(string $name, $value): self
    {
        $this->vars[$name] = $value;
        return $this;
    }

    public function withHandlers(array $handlers): self
    {
        foreach ($handlers as $name => $handler) {
            $this->withHandler($name, $handler);
        }

        return $this;
    }

    public function withHandler(string $name, callable $handler): self
    {
        $this->handlers[$name] = $handler;
        return $this;
    }

    public function withTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigurationException
     */
    public function render(string $text): string
    {
        return preg_replace_callback(
            "/{(?:([^:}]+):)?([^}]+)}/",
            fn (array $m) => $this->renderMatch($m[1], $m[2]),
            $text
        );
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws InvalidConfigurationException
     */
    private function renderMatch(string $tag, string $text): string
    {
        if (strlen($tag) > 0) {
            // render with selector
            if ($this->hasHandler($tag)) {
                $handler = $this->handlers[$tag];

                return ($handler)($text);
            }

            if ($this->hasVar($tag)) {
                $var = $this->vars[$tag];

                return $this->renderByIndex($var, $text);
            }

            // rendering as a first option by default
            return $this->renderByIndex(1, $text);
        }

        // try render as var
        if ($this->hasVar($text)) {
            return $this->vars[$text];
        }

        // default: render by gender
        return $this->renderByIndex($this->gender, $text);
    }

    private function renderByIndex(int $index, string $text): string
    {
        $parts = explode('|', $text);

        return $parts[$index - 1] ?? '';
    }

    private function hasVar(string $name): bool
    {
        return array_key_exists($name, $this->vars);
    }

    private function hasHandler(string $name): bool
    {
        return array_key_exists($name, $this->handlers);
    }
}
