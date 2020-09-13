<?php

namespace Brightwood\Models\Cards\Moves\Actions;

use Brightwood\Models\Cards\Moves\Actions\Interfaces\SkipActionInterface;

class SkipGiftAction extends GiftAction implements SkipActionInterface
{
    /**
     * @return string[] Message lines.
     */
    public function getMessages() : array
    {
        return [
            'Следующий игрок пропускает ход'
        ];
    }
}
