<?php

namespace Brightwood\Models\Nodes;

use App\Models\TelegramUser;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Interfaces\MutatorInterface;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Messages\StoryMessageSequence;

abstract class StaticNode extends StoryNode implements MutatorInterface
{
    /** @var string[] */
    protected array $text;

    /** @var callable|null */
    protected $mutator = null;

    /**
     * @param string[] $text
     */
    public function __construct(
        int $id,
        array $text
    )
    {
        parent::__construct($id);

        $this->text = $text;
    }

    /**
     * @return string[]
     */
    public function text() : array
    {
        return $this->text;
    }

    /**
     * @return static
     */
    public function withMutator(callable $mutator) : self
    {
        $this->mutator = $mutator;

        return $this;
    }

    public function mutate(StoryData $data) : StoryData
    {
        return $this->mutator
            ? ($this->mutator)($data)
            : $data;
    }

    /**
     * Alias for withMutator().
     * 
     * @return static
     */
    public function do(callable $mutator) : self
    {
        return $this->withMutator($mutator);
    }

    public function getMessages(
        TelegramUser $tgUser,
        StoryData $data,
        ?string $text = null
    ) : StoryMessageSequence
    {
        return new StoryMessageSequence(
            new StoryMessage(
                $this->id,
                $this->text,
                null,
                $this->mutate($data)
            )
        );
    }
}
