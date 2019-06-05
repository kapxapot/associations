<?php

namespace App\Generators;

use Plasticode\Exceptions\ApplicationException;
use Plasticode\Generators\EntityGenerator;

use Respect\Validation\Validator as v;

use App\Models\Association;
use App\Models\Game;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;

class TurnsGenerator extends EntityGenerator
{
    public function getRules($data, $id = null)
    {
        $rules = parent::getRules($data, $id);

        $rules['game_id'] = $this
            ->rule('posInt')
            ->gameExists() // v
            ->gameIsCurrent() // v
            ->gameTurnIsCorrect($data['prev_turn_id'] ?? null); // v
        
        if (array_key_exists('prev_turn_id', $data)) {
            $rules['prev_turn_id'] = $this
                ->rule('posInt')
                ->turnExists(); // v
        }
        
        $rules['word'] = $this
            ->rule('text')
            ->length($this->config->wordMinLength(), $this->config->wordMaxLength())
            ->wordIsValid() // v
            ->wordIsNotRepetitive($data['game_id']); // v

        return $rules;
    }
    
    public function beforeSave($data, $id = null)
    {
        $data = parent::beforeSave($data, $id);
        
        $user = $this->auth->getUser();

        $data['user_id'] = $user->getId();
        
        $game = Game::get($data['game_id']);

        if ($game === null) {
            throw new ApplicationException('Game is null.');
        }
        
        $wordStr = $data['word'];

        $language = $game->language();
        
        $data['language_id'] = $language->getId();
        
        $wordStr = $this->languageService->normalizeWord($language, $wordStr);
        
        $word = Word::findInLanguage($language, $wordStr)
            ?? $this->wordService->add($language, $wordStr, $user);
        
        if ($word === null) {
            throw new ApplicationException('Word can\'t be found or added.');
        }
        
        $data['word_id'] = $word->getId();
        
        unset($data['word']);

        // association_id
        if ($game->lastTurn() !== null) {
            $association = Association::getByPair($game->lastTurnWord(), $word, $language)
                ?? $this->associationService->create($game->lastTurnWord(), $word, $user, $language);
            
            if ($association === null) {
                throw new ApplicationException('Association can\'t be found or added.');
            }
            
            $data['association_id'] = $association->getId();
        }

        return $data;
    }
    
    public function afterSave($item, $data)
    {
        parent::afterSave($item, $data);

        $turn = Turn::get($item->id);
        
        if ($turn !== null) {
            $this->gameService->processPlayerTurn($turn);
        }
    }
}
