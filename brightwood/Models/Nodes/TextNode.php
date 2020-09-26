<?php

namespace Brightwood\Models\Nodes;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Interfaces\MutatorInterface;
use Brightwood\Models\Messages\StoryMessage;

abstract class TextNode extends StoryNode implements MutatorInterface
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
    public function withMutator(callable $func) : self
    {
        $this->mutator = $func;
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
    public function do(callable $func) : self
    {
        return $this->withMutator($func);
    }

    public function getMessage(StoryData $data) : StoryMessage
    {
        return (new StoryMessage(
            $this->id,
            $this->text
        ))->withData(
            $this->mutate($data)
        );
    }
}
