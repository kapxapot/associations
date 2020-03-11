<?php

namespace App\Services;

use App\Models\User;
use App\Models\Word;
use App\Models\WordFeedback;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;

class WordFeedbackService
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var ValidationRules */
    private $validationRules;

    /** @var WordService */
    private $wordService;

    public function __construct(
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        WordService $wordService
    )
    {
        $this->validator = $validator;
        $this->validationRules = $validationRules;
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
        $word = Word::get($wordId);
        
        $model =
            WordFeedback::getByWordAndUser($word, $user)
            ??
            WordFeedback::create(
                [
                    'word_id' => $word->getId(),
                    'created_by' => $user->getId(),
                ]
            );
        
        $model->dislike = Convert::toBit($data['dislike'] ?? null);
        
        $typo = Strings::normalize($data['typo'] ?? null);
        $model->typo = (strlen($typo) > 0) ? $typo : null;
        
        $duplicate = Strings::normalize($data['duplicate'] ?? null);
        $duplicateWord = Word::findInLanguage($word->language(), $duplicate);
        
        $model->duplicateId = ($duplicateWord !== null)
            ? $duplicateWord->getId()
            : null;
        
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
                ->wordExists()
        ];
        
        if (($data['typo'] ?? null) !== null) {
            $result['typo'] = $this->wordService->getRule();
        }
        
        if (($data['duplicate'] ?? null) !== null) {
            $word = Word::get($data['word_id'] ?? null);
            
            if ($word !== null) {
                $result['duplicate'] =
                    Validator::mainWordExists($word->language(), $word);
            }
        }
        
        return $result;
    }
}
