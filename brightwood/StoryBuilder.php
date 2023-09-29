<?php

namespace Brightwood;

use Brightwood\Models\Links\RedirectLink;
use Brightwood\Models\Nodes\ActionNode;
use Brightwood\Models\Nodes\FinishNode;
use Brightwood\Models\Nodes\FunctionNode;
use Brightwood\Models\Nodes\RedirectNode;
use Brightwood\Models\Nodes\SkipNode;
use Brightwood\Models\Stories\Story;

class StoryBuilder
{
    private Story $story;

    public function __construct(Story $story)
    {
        $this->story = $story;
    }

    /**
     * @param string|string[] $text
     * @param array<int, string> $links NodeId -> Text
     */
    public function addActionNode(int $id, $text, array $links): ActionNode
    {
        $text = $this->arraify($text);
        $node = new ActionNode($id, $text, $links);
        $this->story->addNode($node);

        return $node;
    }

    /**
     * @param string|string[]|null $text
     */
    public function addFinishNode(int $id, $text = null): FinishNode
    {
        $text = $this->arraify($text);
        $node = new FinishNode($id, $text);

        $this->story->addNode($node);

        return $node;
    }

    /**
     * @param string|string[] $text
     * @param (RedirectLink|array|int)[] $links
     *
     * @throws InvalidArgumentException
     */
    public function addRedirectNode(int $id, $text, array $links): RedirectNode
    {
        $text = $this->arraify($text);
        $node = new RedirectNode($id, $text, $links);

        $this->story->addNode($node);

        return $node;
    }

    /**
     * @param string|string[]|null $text
     */
    public function addSkipNode(int $id, int $nextNodeId, $text = null): SkipNode
    {
        $text = $this->arraify($text);
        $node = new SkipNode($id, $nextNodeId, $text);

        $this->story->addNode($node);

        return $node;
    }

    public function addFunctionNode(
        int $id,
        callable $actionFunc,
        ?callable $finishFunc = null
    ): FunctionNode
    {
        $node = new FunctionNode($id, $actionFunc, $finishFunc);

        $this->story->addNode($node);

        return $node;
    }

    public function redirects(
        int $nodeId,
        ?float $weight = null
    ): RedirectLink
    {
        return new RedirectLink($nodeId, $weight);
    }

    /**
     * @param int|array $data
     * @param callable $condition
     */
    public function redirectsIf($data, callable $condition): RedirectLink
    {
        if (is_array($data)) {
            [$nodeId, $weight] = $data;
        } else {
            $nodeId = $data;
            $weight = null;
        }

        return $this->redirects($nodeId, $weight)->if($condition);
    }

    /**
     * @param string|string[]|null $text
     * @return string[]|null
     */
    private function arraify($text): ?array
    {
        if ($text === null) {
            return null;
        }

        return is_array($text)
            ? $text
            : [$text];
    }
}
