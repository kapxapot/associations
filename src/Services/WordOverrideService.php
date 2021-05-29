<?php

namespace App\Services;

use App\Events\Override\WordOverrideCreatedEvent;
use App\Models\User;
use App\Models\WordOverride;
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Util\Convert;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;

class WordOverrideService
{
    private WordOverrideRepositoryInterface $wordOverrideRepository;
    private WordRepositoryInterface $wordRepository;

    private LanguageService $languageService;
    private WordService $wordService;

    private ValidatorInterface $validator;
    private ValidationRules $validationRules;

    private EventDispatcher $eventDispatcher;

    public function __construct(
        WordOverrideRepositoryInterface $wordOverrideRepository,
        WordRepositoryInterface $wordRepository,
        LanguageService $languageService,
        WordService $wordService,
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        EventDispatcher $eventDispatcher
    )
    {
        $this->wordOverrideRepository = $wordOverrideRepository;
        $this->wordRepository = $wordRepository;

        $this->languageService = $languageService;
        $this->wordService = $wordService;

        $this->validator = $validator;
        $this->validationRules = $validationRules;

        $this->eventDispatcher = $eventDispatcher;
    }

    public function save(array $data, User $user): WordOverride
    {
        $override = $this->toModel($data, $user);

        $override = $this
            ->wordOverrideRepository
            ->save($override);

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

        $model = $this->wordOverrideRepository->create([
            'word_id' => $word->getId(),
            'created_by' => $user->getId(),
        ]);

        /** @var bool|null $approved */
        $approved = $data['approved'] ?? null;

        if ($approved !== null) {
            $model->approved = Convert::toBit($approved);
        }

        /** @var bool|null $mature */
        $mature = $data['mature'] ?? null;

        if ($mature !== null) {
            $model->mature = Convert::toBit($mature);
        }

        $model->disabled = Convert::toBit($data['disabled'] ?? null);

        $wordCorrection = $this->languageService->normalizeWord(
            $word->language(),
            $data['word_correction'] ?? null
        );

        $model->wordCorrection = (strlen($wordCorrection) > 0) ? $wordCorrection : null;

        $posCorrection = $data['pos_correction'] ?? [];

        if (!empty($posCorrection)) {
            $model->posCorrection = implode(
                WordOverride::POS_DELIMITER,
                $posCorrection
            );
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
                    ->wordCorrectionNotEqualsWord(
                        $this->languageService,
                        $word
                    )
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
