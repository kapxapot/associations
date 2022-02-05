<?php

namespace App\Validation\Rules;

use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use Respect\Validation\Rules\AbstractRule;

class WordRelationTypeAllowsPrimaryRelation extends AbstractRule
{
    private WordRelationTypeRepositoryInterface $wordRelationTypeRepository;

    public function __construct(
        WordRelationTypeRepositoryInterface $wordRelationTypeRepository
    )
    {
        $this->wordRelationTypeRepository = $wordRelationTypeRepository;
    }

    /**
     * @param string|null $input
     * @return boolean
     */
    public function validate($input)
    {
        if (!is_numeric($input)) {
            return false;
        }

        $typeId = intval($input);
        $type = $this->wordRelationTypeRepository->get($typeId);

        return $type !== null && !$type->isSecondary();
    }
}
