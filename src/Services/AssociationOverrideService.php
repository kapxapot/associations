<?php

namespace App\Services;

use App\Events\Override\AssociationOverrideCreatedEvent;
use App\Models\AssociationOverride;
use App\Models\User;
use App\Repositories\Interfaces\AssociationOverrideRepositoryInterface;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Util\Convert;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;

class AssociationOverrideService
{
    private AssociationOverrideRepositoryInterface $associationOverrideRepository;
    private AssociationRepositoryInterface $associationRepository;

    private ValidatorInterface $validator;
    private ValidationRules $validationRules;

    private EventDispatcher $eventDispatcher;

    public function __construct(
        AssociationOverrideRepositoryInterface $associationOverrideRepository,
        AssociationRepositoryInterface $associationRepository,
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        EventDispatcher $eventDispatcher
    )
    {
        $this->associationOverrideRepository = $associationOverrideRepository;
        $this->associationRepository = $associationRepository;

        $this->validator = $validator;
        $this->validationRules = $validationRules;

        $this->eventDispatcher = $eventDispatcher;
    }

    public function save(array $data, User $user): AssociationOverride
    {
        $override = $this->toModel($data, $user);

        $override = $this
            ->associationOverrideRepository
            ->save($override);

        $event = new AssociationOverrideCreatedEvent($override);
        $this->eventDispatcher->dispatch($event);

        return $override;
    }

    public function toModel(array $data, User $user): AssociationOverride
    {
        $this->validate($data);

        return $this->convertToModel($data, $user);
    }

    private function convertToModel(array $data, User $user): AssociationOverride
    {
        $associationId = $data['association_id'];
        $association = $this->associationRepository->get($associationId);

        $model = $this->associationOverrideRepository->create([
            'association_id' => $association->getId(),
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
        return [
            'association_id' => $this
                ->validationRules
                ->get('posInt')
                ->associationExists($this->associationRepository)
        ];
    }
}
