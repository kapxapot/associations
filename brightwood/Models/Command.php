<?php

namespace Brightwood\Models;

use Webmozart\Assert\Assert;

/**
 * Command abstraction for Telegram.
 */
class Command
{
    private string $code;
    private ?string $label = null;

    /**
     * @param string $code Command code without '/'.
     * @param string|null $label Command description.
     */
    public function __construct(
        string $code,
        ?string $label = null
    )
    {
        Assert::stringNotEmpty($code);

        $this->code = $code;

        if (strlen($label) > 0) {
            $this->label = $label;
        }
    }

    public function code(): string
    {
        return $this->code;
    }

    public function label(): ?string
    {
        return $this->label;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $codeStr = $this->codeString();

        return $this->label
            ? $this->label . ' ' . $codeStr
            : $codeStr;
    }

    public function codeString(): string
    {
        return '/' . $this->code;
    }
}
