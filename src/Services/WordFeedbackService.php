<?php

namespace App\Services;

use App\Models\User;
use App\Models\WordFeedback;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;

class WordFeedbackService
{
    private ValidatorInterface $validator;
    private ValidationRules $validationRules;
    private WordFeedbackRepositoryInterface $wordFeedbackRepository;
    private WordRepositoryInterface $wordRepository;
    private WordService $wordService;

    public function __construct(
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        WordFeedbackRepositoryInterface $wordFeedbackRepository,
        WordRepositoryInterface $wordRepository,
        WordService $wordService
    )
    {
        $this->validator = $validator;
        $this->validationRules = $validationRules;
        $this->wordFeedbackRepository = $wordFeedbackRepository;
        $this->wordRepository = $wordRepository;
        $this->wordService = $wordService;
    }

    public function toModel(array $data, User $user) : WordFeedback
    {
        $this->validate($data);
        
        return $this->convertToModel($data, $user);
    }

    private function convertToModel(array $data, User $user) : WordFeedback
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

        $model->duplicateId = ($duplicateWord !== null)
            ? $duplicateWord->getId()
            : null;

        $model->withDuplicate($duplicateWord);

        $model->mature = Convert::toBit($data['mature'] ?? null);

        if ($model->isPersisted()) {
            $model->updatedAt = Date::dbNow();
        }
        
        return $model;
    }

    private function validate(array $data)
    {
        $rules = $this->getRules($data);

        $this
            ->validator
            ->validateArray($data, $rules)
            ->throwOnFail();
    }

    private function getRules(array $data) : array
    {
        $result = [
            'word_id' => $this
                ->validationRules
                ->get('posInt')
                ->wordExists($this->wordRepository)
        ];
        
        if (($data['typo'] ?? null) !== null) {
            $result['typo'] = $this->wordService->getRule();
        }
        
        if (($data['duplicate'] ?? null) !== null) {
            $word = $this
                ->wordRepository
                ->get($data['word_id'] ?? null);
            
            if ($word) {
                $result['duplicate'] =
                    Validator::mainWordExists(
                        $this->wordRepository,
                        $word->language(),
                        $word
                    );
            }
        }
        
        return $result;
    }
}
