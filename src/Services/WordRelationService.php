<?php

namespace App\Services;

use App\Events\Word\WordPrimaryRelationChangedEvent;
use App\Events\WordRelation\WordRelationUpdatedEvent;
use App\Models\User;
use App\Models\WordRelation;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;

class WordRelationService
{
    private WordRelationRepositoryInterface $wordRelationRepository;
    private WordRelationTypeRepositoryInterface $wordRelationTypeRepository;
    private WordRepositoryInterface $wordRepository;

    private ValidatorInterface $validator;
    private ValidationRules $validationRules;

    private EventDispatcher $eventDispatcher;

    public function __construct(
        WordRelationRepositoryInterface $wordRelationRepository,
        WordRelationTypeRepositoryInterface $wordRelationTypeRepository,
        WordRepositoryInterface $wordRepository,
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        EventDispatcher $eventDispatcher
    )
    {
        $this->wordRelationRepository = $wordRelationRepository;
        $this->wordRelationTypeRepository = $wordRelationTypeRepository;
        $this->wordRepository = $wordRepository;

        $this->validator = $validator;
        $this->validationRules = $validationRules;

        $this->eventDispatcher = $eventDispatcher;
    }

    public function save(array $data, User $user): WordRelation
    {
        $relation = $this->toModel($data, $user);

        return $this->saveRelation($relation);
    }

    public function saveRelation(WordRelation $relation): WordRelation
    {
        $relation = $this
            ->wordRelationRepository
            ->save($relation);

        $event = new WordRelationUpdatedEvent($relation);
        $this->eventDispatcher->dispatch($event);

        if ($relation->isPrimary()) {
            $this->updateSiblingRelationsFor($relation);

            $primaryEvent = new WordPrimaryRelationChangedEvent($relation->word());
            $this->eventDispatcher->dispatch($primaryEvent);
        }

        return $relation;
    }

    /**
     * If the relation is set as primary, and there was a primary relation before,
     * it must be set as non-primary.
     */
    private function updateSiblingRelationsFor(WordRelation $relation): void
    {
        $word = $relation->word();

        $siblingRelations = $word
            ->relations()
            ->except($relation);

        /** @var WordRelation|null */
        $previousPrimaryRelation = $siblingRelations->first(
            fn (WordRelation $wr) => $wr->isPrimary()
        );

        if ($previousPrimaryRelation === null) {
            return;
        }

        $previousPrimaryRelation->primary = false;
        $this->saveRelation($previousPrimaryRelation);
    }

    public function toModel(array $data, User $user): WordRelation
    {
        $this->validate($data);

        return $this->convertToModel($data, $user);
    }

    private function convertToModel(array $data, User $user): WordRelation
    {
        $id = $data['id'] ?? null;
        $wordId = $data['word_id'];

        if ($id !== null) {
            $model = $this->wordRelationRepository->get($id);
        } else {
            $model = $this->wordRelationRepository->create([
                'word_id' => $wordId,
                'created_by' => $user->getId(),
            ]);
        }

        $model->typeId = $data['type_id'];

        $word = $this->wordRepository->get($wordId);

        $mainWord = $this->wordRepository->findInLanguage(
            $word->language(),
            $data['main_word']
        );

        $model->mainWordId = $mainWord->getId();

        $primary = $data['primary'] ?? false;
        $model->primary = Convert::toBit($primary);

        $model->updatedBy = $user->getId();

        if ($model->isPersisted()) {
            $model->updatedAt = Date::dbNow();
        }

        return $model;
    }

    private function validate(array $data): void
    {
        $rules = $this->getRules($data);

        $this
            ->validator
            ->validateArray($data, $rules)
            ->throwOnFail();
    }

    private function getRules(array $data): array
    {
        $result = [
            'type_id' => $this
                ->validationRules
                ->get('posInt')
                ->wordRelationTypeExists($this->wordRelationTypeRepository),
            'word_id' => $this
                ->validationRules
                ->get('posInt')
                ->wordExists($this->wordRepository),
        ];

        $word = $this->wordRepository->get($data['word_id'] ?? null);

        if ($word !== null) {
            $result['main_word'] =
                Validator::mainWordExists(
                    $this->wordRepository,
                    $word->language(),
                    $word
                );
        }

        return $result;
    }
}
