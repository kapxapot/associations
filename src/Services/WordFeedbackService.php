<?php

namespace App\Services;

use Respect\Validation\Validator as v;

use Plasticode\Contained;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;
use Plasticode\Validation\ValidationRules;

use App\Models\Word;
use App\Models\WordFeedback;

class WordFeedbackService extends Contained
{
    public function toModel(array $data) : WordFeedback
    {
		$this->validate($data);
		
        return $this->convertToModel($data);
    }

	private function convertToModel(array $data) : WordFeedback
	{
	    $wordId = $data['word_id'];
	    $word = Word::get($wordId);
	    
	    $user = $this->auth->getUser();
        
        $model =
            WordFeedback::getByWordAndUser($word, $user)
            ??
            WordFeedback::create([
                'word_id' => $word->getId(),
                'created_by' => $user->getId(),
            ]);
        
        $model->dislike = (($data['dislike'] ?? null) === true) ? 1 : 0;
        
        $typo = Strings::normalize($data['typo'] ?? null);
        $model->typo = (strlen($typo) > 0) ? $typo : null;
        
        $duplicate = Strings::normalize($data['duplicate'] ?? null);
        $duplicateWord = Word::findInLanguage($word->language(), $duplicate);
        $model->duplicateId = ($duplicateWord !== null) ? $duplicateWord->getId() : null;
        
        $model->mature = (($data['mature'] ?? null) === true) ? 1 : 0;

        if ($model->isPersisted()) {
            $model->updatedAt = Date::dbNow();
        }
        
        return $model;
	}

	private function validate(array $data)
	{
	    $rules = $this->getRules($data);
		$validation = $this->validator->validateArray($data, $rules);
		
		if ($validation->failed()) {
			throw new ValidationException($validation->errors);
		}
	}

	private function getRules(array $data) : array
	{
		$rules = new ValidationRules($this->container);

		$result = [
			'word_id' => $rules->get('posInt')
			    ->wordExists(),
		];
		
		if (($data['typo'] ?? null) !== null) {
			$result['typo'] = $rules->get('text')
                ->length($this->config->wordMinLength(), $this->config->wordMaxLength())
                ->wordIsValid();
		}
		
		if (($data['duplicate'] ?? null) !== null) {
		    $word = Word::get($data['word_id'] ?? null);
		    
		    if ($word !== null) {
    		    $result['duplicate'] = v::wordByWordExists($word->language(), $word);
		    }
		}
		
		return $result;
	}
}
