<?php

namespace App\Services;

use App\Events\Override\WordOverrideCreatedEvent;
use App\Models\User;
use App\Models\WordOverride;
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;

class WordOverrideService
{
    private WordOverrideRepositoryInterface $wordOverrideRepository;
    private WordRepositoryInterface $wordRepository;

    private ValidatorInterface $validator;
    private ValidationRules $validationRules;
    private WordService $wordService;

    private EventDispatcher $eventDispatcher;

    public function __construct(
        WordOverrideRepositoryInterface $wordOverrideRepository,
        WordRepositoryInterface $wordRepository,
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        WordService $wordService,
        EventDispatcher $eventDispatcher
    )
    {
        $this->wordOverrideRepository = $wordOverrideRepository;
        $this->wordRepository = $wordRepository;

        $this->validator = $validator;
        $this->validationRules = $validationRules;
        $this->wordService = $wordService;

        $this->eventDispatcher = $eventDispatcher;
    }

    public function save(array $data, User $user): WordOverride
    {
        $override = $this->toModel($data, $user);

        $override = $this
            ->wordOverrideRepository
            ->create($override->toArray());

        $event = new WordOverrideCreatedEvent($override);
        $this->eventDispatcher->dispatch($event);

        return $override;
    }

    public function toModel(array $data, User $user): WordOverride
    {
        $this->validate($data);

        return $this->convertToModel($data, $user);
    }

    private function convertToModel(array $data, User $user): WordOverride
    {
        $wordId = $data['word_id'];
        $word = $this->wordRepository->get($wordId);

        $model =
            $word->feedbackBy($user)
            ??
            $this->wordFeedbackRepository->create(
                [
                    'word_id' => $word->getId(),
                    'created_by' => $user->getId(),
                ]
            );

        $model->dislike = Convert::toBit($data['dislike'] ?? null);

        $typo = Strings::normalize($data['typo'] ?? null);
        $model->typo = (strlen($typo) > 0) ? $typo : null;

        $duplicate = Strings::normalize($data['duplicate'] ?? null);

        $duplicateWord = $this
            ->wordRepository
            ->findInLanguage(
                $word->language(),
                $duplicate
            );

        $model->duplicateId = $duplicateWord
            ? $duplicateWord->getId()
            : null;

        $model->withDuplicate($duplicateWord);

        $model->mature = Convert::toBit($data['mature'] ?? null);

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
        $word = $this->wordRepository->get($data['word_id'] ?? null);

        $result = [
            'word_id' => $this
                ->validationRules
                ->get('posInt')
                ->wordExists($this->wordRepository)
        ];

        if (($data['word_correction'] ?? null) !== null) {
            $wordCorrectionRule = $this->wordService->getRule();

            if ($word) {
                $wordCorrectionRule = $wordCorrectionRule
                    ->wordCorrectionNotEqualsWord($word)
                    ->wordAvailable(
                        $this->wordRepository,
                        $word->language(),
                        $word->getId()
                    );
            }

            $result['word_correction'] = $wordCorrectionRule;
        }

        return $result;
    }
}
