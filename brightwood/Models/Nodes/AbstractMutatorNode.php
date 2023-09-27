<?php

namespace Brightwood\Models\Nodes;

use App\Models\TelegramUser;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Interfaces\MutatorInterface;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Traits\Mutator;

/**
 * @method $this withMutator(callable $mutator)
 * @method $this do(callable $mutator)
 */
abstract class AbstractMutatorNode extends AbstractTextNode implements MutatorInterface
{
    use Mutator;

    public function getMessages(
        TelegramUser $tgUser,
        StoryData $data,
        ?string $input = null
    ): StoryMessageSequence
    {
        return parent::getMessages($tgUser, $this->mutate($data), $input);
    }
}
