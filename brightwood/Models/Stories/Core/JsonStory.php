<?php

namespace Brightwood\Models\Stories\Core;

use Brightwood\Models\Data\JsonStoryData;
use Brightwood\Models\Nodes\AbstractStoryNode;
use Brightwood\StoryBuilder;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class JsonStory extends Story
{
    private ?array $values = null;

    public function __construct(Story $story)
    {
        parent::__construct($story->toArray());

        $this
            ->withCurrentVersion(fn () => $story->currentVersion())
            ->withCreator(fn () => $story->creator());

        $this->prepare();
    }

    private function getValue(string $key)
    {
        if (!$this->values) {
            $this->values = $this->currentVersion()
                ? json_decode($this->currentVersion()->jsonData, true)
                : [];
        }

        return $this->values[$key] ?? null;
    }

    public function title(): string
    {
        return $this->getValue('title') ?? parent::title();
    }

    public function description(): string
    {
        return $this->getValue('description') ?? parent::description();
    }

    public function makeData(?array $data = null): JsonStoryData
    {
        return new JsonStoryData(
            $this->getValue('data') ?? null,
            $data
        );
    }

    protected function build(): void
    {
        $this->validate();

        $startId = $this->getValue('startId');
        $prefix = $this->getValue('prefix');
        $nodesData = $this->getValue('nodes');

        if ($prefix) {
            $this->setPrefixMessage($prefix);
        }

        $builder = new StoryBuilder($this);

        foreach ($nodesData as $datum) {
            $node = $this->buildNode($builder, $datum);

            if ($node->id() == $startId) {
                $this->setStartNode($node);
            }
        }
    }

    private function buildNode(StoryBuilder $builder, array $data): AbstractStoryNode
    {
        $id = (int)$data['id'];
        $type = $data['type'];

        switch ($type) {
            case 'action':
                return $builder->addActionNode(
                    $id,
                    $data['text'] ?? [],
                    array_reduce(
                        $data['actions'],
                        function ($result, $item) {
                            $result[$item['id']] = $item['label'];
                            return $result;
                        },
                        []
                    )
                );

            case 'finish':
                return $builder->addFinishNode(
                    $id,
                    $data['text'] ?? null
                );

            case 'skip':
                return $builder->addSkipNode(
                    $id,
                    $data['nextId'],
                    $data['text'] ?? null
                );

            case 'redirect':
                return $builder->addRedirectNode(
                    $id,
                    $data['text'] ?? [],
                    array_map(
                        function ($item) {
                            $linkId = $item['id'];
                            $weight = $item['weight'] ?? null;

                            return $weight ? [$linkId, $weight] : $linkId;
                        },
                        $data['links']
                    )
                );
        }

        throw new InvalidArgumentException(
            sprintf('Unknown node [%s] type: %s', $id, $type)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function validate(): void
    {
        $startId = $this->getValue('startId');
        Assert::notNull($startId, '`startId` is undefined.');

        $nodesData = $this->getValue('nodes') ?? [];
        Assert::notEmpty($nodesData, 'No nodes defined.');

        $foundStartNode = false;
        $foundIds = [];

        for ($ni = 0; $ni < count($nodesData); $ni++) {
            $node = $nodesData[$ni];

            $id = $node['id'] ?? null;
            $type = $node['type'] ?? null;

            Assert::notEmpty(
                $id,
                sprintf('Node [index: %s]: `id` is undefined.', $ni)
            );

            Assert::notEmpty(
                $type,
                sprintf('Node [%s]: `type` is undefined.', $id)
            );

            if ($id === $startId) {
                $foundStartNode = true;
            }

            Assert::false(
                in_array($id, $foundIds),
                sprintf('Duplicate node id: %s.', $id)
            );

            $foundIds[] = $id;

            switch ($type) {
                case 'action':
                    $actions = $node['actions'] ?? [];

                    Assert::notEmpty(
                        $actions,
                        sprintf('Action node [%s]: no actions defined.', $id)
                    );

                    for ($ai = 0; $ai < count($actions); $ai++) {
                        $action = $actions[$ai];
                        $aid = $action['id'] ?? null;
                        $label = $action['label'] ?? null;

                        Assert::notEmpty(
                            $aid,
                            sprintf('Action node [%s], action [index: %s]: `id` is undefined.', $id, $ai)
                        );

                        Assert::notEmpty(
                            $label,
                            sprintf('Action node [%s], action [%s]: `label` is undefined.', $id, $aid)
                        );
                    }

                    break;

                case 'finish':
                    // nothing to validate
                    break;

                case 'skip':
                    $nextId = $node['nextId'] ?? null;

                    Assert::notEmpty(
                        $nextId,
                        sprintf('Skip node [%s]: `nextId` is undefined.', $id)
                    );

                    break;

                case 'redirect':
                    $links = $node['links'] ?? [];

                    Assert::notEmpty(
                        $links,
                        sprintf('Redirect node [%s]: no links defined.', $id)
                    );

                    for ($li = 0; $li < count($links); $li++) {
                        $link = $links[$li];
                        $lid = $link['id'] ?? null;
                        $weight = $link['weight'] ?? null;

                        Assert::notEmpty(
                            $lid,
                            sprintf('Redirect node [%s], link [index: %s]: `id` is undefined.', $id, $li)
                        );

                        if ($weight !== null) {
                            Assert::numeric(
                                $weight,
                                sprintf('Redirect node [%s], link [%s]: `weight` must be a number (integer or float).', $id, $lid)
                            );

                            Assert::greaterThan(
                                $weight,
                                0,
                                sprintf('Redirect node [%s], link [%s]: `weight` must be positive.', $id, $lid)
                            );
                        }
                    }

                    break;
            }
        }

        Assert::true(
            $foundStartNode,
            sprintf('Start node [%s] is undefined.', $startId)
        );
    }

    public function checkIntegrity(): void
    {
        parent::checkIntegrity();
        $this->validate();
    }
}
