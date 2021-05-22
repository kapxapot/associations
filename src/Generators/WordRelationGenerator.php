<?php

namespace App\Generators;

use App\Events\Word\WordRelationsChangedEvent;
use App\Models\WordRelation;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\ChangingEntityGenerator;
use Plasticode\Util\Convert;
use Plasticode\Util\Strings;
use Respect\Validation\Validator;

/**
 * @emits WordRelationsChangedEvent
 */
class WordRelationGenerator extends ChangingEntityGenerator
{
    private WordRepositoryInterface $wordRepository;
    private WordRelationRepositoryInterface $wordRelationRepository;
    private WordRelationTypeRepositoryInterface $wordRelationTypeRepository;

    private EventDispatcher $eventDispatcher;

    public function __construct(
        GeneratorContext $context,
        WordRepositoryInterface $wordRepository,
        WordRelationRepositoryInterface $wordRelationRepository,
        WordRelationTypeRepositoryInterface $wordRelationTypeRepository,
        EventDispatcher $eventDispatcher
    )
    {
        parent::__construct($context);

        $this->wordRepository = $wordRepository;
        $this->wordRelationRepository = $wordRelationRepository;
        $this->wordRelationTypeRepository = $wordRelationTypeRepository;

        $this->eventDispatcher = $eventDispatcher;
    }

    protected function entityClass(): string
    {
        return WordRelation::class;
    }

    protected function getRepository(): WordRelationRepositoryInterface
    {
        return $this->wordRelationRepository;
    }

    public function getRules(array $data, $id = null): array
    {
        $rules = array_merge(
            parent::getRules($data, $id),
            [
                'type_id' => $this
                    ->rule('posInt')
                    ->wordRelationTypeExists($this->wordRelationTypeRepository),
                'word_id' => $this
                    ->rule('posInt')
                    ->wordExists($this->wordRepository),
            ]
        );

        $word = $this->wordRepository->get($data['word_id'] ?? null);

        if ($word !== null) {
            $rules['main_word'] =
                Validator::mainWordExists(
                    $this->wordRepository,
                    $word->language(),
                    $word
                );
        }

        return $rules;
    }

    public function beforeSave(array $data, $id = null): array
    {
        $wordId = $data['word_id'];

        $word = $this->wordRepository->get($wordId);

        $mainWord = $this->wordRepository->findInLanguage(
            $word->language(),
            Strings::normalize($data['main_word'])
        );

        $data['main_word_id'] = $mainWord->getId();

        unset($data['main_word']);

        $primary = $data['primary'] ?? false;
        $data['primary'] = Convert::toBit($primary);

        return $data;
    }

    public function afterSave(array $item, array $data): void
    {
        $this->processChange($item);
    }

    public function afterDelete(array $item): void
    {
        $this->processChange($item);
    }

    private function processChange(array $item): void
    {
        $word = $this->wordRepository->get($item['word_id']);

        $event = new WordRelationsChangedEvent($word);
        $this->eventDispatcher->dispatch($event);
    }
}
