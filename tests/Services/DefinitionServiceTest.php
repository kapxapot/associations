<?php

namespace App\Tests\Services;

use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\DefinitionService;
use App\Testing\Factories\WordRepositoryFactory;
use App\Testing\Mocks\External\DefinitionSourceMock;
use App\Testing\Mocks\Repositories\DefinitionRepositoryMock;
use PHPUnit\Framework\TestCase;
use Plasticode\Events\EventDispatcher;

final class DefinitionServiceTest extends TestCase
{
    private WordRepositoryInterface $wordRepository;
    private DefinitionService $definitionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wordRepository = WordRepositoryFactory::make();

        $this->definitionService = new DefinitionService(
            new DefinitionRepositoryMock(
                $this->wordRepository
            ),
            new DefinitionSourceMock(),
            new EventDispatcher()
        );
    }

    protected function tearDown(): void
    {
        unset($this->definitionService);
        unset($this->wordRepository);

        parent::tearDown();
    }

    public function testGetByWordRemoteValid(): void
    {
        $word = $this->wordRepository->get(1);

        $definition = $this->definitionService->getByWord($word, true);

        $this->assertNotNull($definition);
        $this->assertTrue($definition->word()->equals($word));
        $this->assertTrue($word->definition()->equals($definition));
        $this->assertTrue($definition->isValid());
    }

    public function testGetByWordRemoteInvalid(): void
    {
        $word = $this->wordRepository->get(2);

        $definition = $this->definitionService->getByWord($word, true);

        $this->assertNotNull($definition);
        $this->assertTrue($definition->word()->equals($word));
        $this->assertTrue($word->definition()->equals($definition));
        $this->assertFalse($definition->isValid());
    }
}
