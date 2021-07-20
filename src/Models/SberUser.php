<?php

namespace App\Models;

/**
 * @property string $sberId
 */
class SberUser extends AbstractBotUser
{
    // NamedInterface

    public function name(): string
    {
        return 'Сбер ' . $this->getId();
    }
}
