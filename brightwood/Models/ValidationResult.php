<?php

namespace Brightwood\Models;

use Brightwood\Models\Messages\StoryMessageSequence;

class ValidationResult
{
    private bool $ok;

    private StoryMessageSequence $errors;

    private function __construct(bool $ok, ?StoryMessageSequence $errors = null)
    {
        $this->ok = $ok;
        $this->errors = $errors ?? StoryMessageSequence::empty();
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function isError(): bool
    {
        return !$this->isOk();
    }

    public function errors(): StoryMessageSequence
    {
        return $this->errors;
    }

    public static function ok(): ValidationResult
    {
        return new ValidationResult(true);
    }

    public static function error(StoryMessageSequence $errors): ValidationResult
    {
        return new ValidationResult(false, $errors);
    }
}
