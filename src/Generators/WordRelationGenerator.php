<?php

namespace App\Generators;

use App\Events\Word\WordRelationsChangedEvent;
use App\Models\WordRelation;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\LanguageService;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\ChangingEntityGenerator;
use Plasticode\Util\Convert;
use Respect\Validation\Validator;

/**
 * This generator is for entity management via the front-end.
 *
 * @emits WordRelationsChangedEvent
 */
class WordRelationGenerator extends ChangingEntityGenerator
{
    private WordRepositoryInterface $wordRepository;
    private WordRelationRepositoryInterface $wordRelationRepository;
    private WordRelationTypeRepositoryInterface $wordRelationTypeRepository;

    private LanguageService $languageService;

    private TranslatorInterface $translator;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        GeneratorContext $context,
        WordRepositoryInterface $wordRepository,
        WordRelationRepositoryInterface $wordRelationRepository,
        WordRelationTypeRepositoryInterface $wordRelationTypeRepository,
        LanguageService $languageService,
        TranslatorInterface $translator,
        EventDispatcher $eventDispatcher
    )
    {
        parent::__construct($context);

        $this->wordRepository = $wordRepository;
        $this->wordRelationRepository = $wordRelationRepository;
        $this->wordRelationTypeRepository = $wordRelationTypeRepository;

        $this->languageService = $languageService;

        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function entityClass(): string
    {
        return WordRelation::class;
    }

    public function getRepository(): WordRelationRepositoryInterface
    {
        return $this->wordRelationRepository;
    }

    public function getRules(array $data, $id = null): array
    {
        $wordId = $data['word_id'] ?? null;
        $primary = $data['primary'] ?? false;
        $typeId = $data['type_id'] ?? null;
        $mainWord = $data['main_word'] ?? null;

        $rules = parent::getRules($data, $id);

        // word rules
        $wordRules = $this
            ->rule('posInt')
            ->wordExists($this->wordRepository);

        if ($typeId !== null && strlen($mainWord) > 0) {
            $wordRules = $wordRules->wordRelationAvailable(
                $this->wordRepository,
                $this->wordRelationRepository,
                $this->wordRelationTypeRepository,
                $this->languageService,
                $typeId,
                $mainWord,
                $id
            );
        }

        $rules['word_id'] = $wordRules;

        // type rules
        $typeRules = $this
            ->rule('posInt')
            ->wordRelationTypeExists($this->wordRelationTypeRepository);

        if ($primary === true) {
            $typeRules = $typeRules->wordRelationTypeAllowsPrimaryRelation(
                $this->wordRelationTypeRepository
            );
        }

        $rules['type_id'] = $typeRules;

        // main word rules
        $word = $this->wordRepository->get($wordId);

        if ($word !== null) {
            $mainWordRules = Validator::mainWordExists($this->languageService, $word);

            if ($primary === true) {
                $mainWordRules = $mainWordRules
                    ->mainWordNotRecursive($this->languageService, $word);
            }

            $rules['main_word'] = $mainWordRules;
        }

        return $rules;
    }

    public function getOptions(): array
    {
        $options = parent::getOptions();

        $options['no_default_uri'] = true;
        $options['uri'] = 'words/{id:\d+}/relations';
        $options['filter'] = 'word_id';

        return $options;
    }

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $relation = $this->getRepository()->get($id);

        if ($relation !== null) {
            $typeName = $relation->type()->name;

            $item['type'] = $typeName;
            $item['localized_type'] = $this->translator->translate($typeName);
            $item['word'] = $relation->word()->word;
            $item['main_word'] = $relation->mainWord()->word;
        }

        return $item;
    }

    public function beforeSave(array $data, $id = null): array
    {
        $wordId = $data['word_id'];

        $word = $this->wordRepository->get($wordId);
        $language = $word->language();

        $mainWord = $this->languageService->findWord($language, $data['main_word']);

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
