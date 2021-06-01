<?php

namespace App\Generators;

use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\EntityGenerator;

class LanguageGenerator extends EntityGenerator
{
    private LanguageRepositoryInterface $languageRepository;

    public function __construct(
        GeneratorContext $context,
        LanguageRepositoryInterface $languageRepository
    )
    {
        parent::__construct($context);

        $this->languageRepository = $languageRepository;
    }

    protected function entityClass(): string
    {
        return Language::class;
    }

    public function getRepository(): LanguageRepositoryInterface
    {
        return $this->languageRepository;
    }
}
