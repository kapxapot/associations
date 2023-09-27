<?php

namespace Brightwood\Models\Nodes;

use App\Models\TelegramUser;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Messages\StoryMessageSequence;

abstract class AbstractTextNode extends AbstractStoryNode
{
    /**
     * @var string[]
     */
    protected array $text;

    /**
     * @param string[]|null $text
     */
    public function __construct(int $id, ?array $text = null)
    {
        parent::__construct($id);

        $this->text = $text ?? [];
    }

    public function getMessages(
        TelegramUser $tgUser,
        StoryData $data,
        ?string $input = null
    ): StoryMessageSequence
    {
        return new StoryMessageSequence(
            new StoryMessage(
                $this->id, $this->text, null, $data
            )
        );
    }
}
