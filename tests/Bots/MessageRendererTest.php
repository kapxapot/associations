<?php

namespace App\Tests\Bots;

use App\Bots\Command;
use App\Bots\Factories\MessageRendererFactory;
use App\Bots\Interfaces\MessageRendererInterface;
use PHPUnit\Framework\TestCase;
use Plasticode\Semantics\Gender;
use Plasticode\Util\Classes;

/**
 * Must render:
 *
 * vars:
 * - hello
 * - word_limit
 *
 * handlers:
 * - cmd - render command, e.g. "«помощь»"
 * - att - вы/ты
 * - q - «»
 * - (void) - genders - use assistant's gender, default = FEM
 */
final class MessageRendererTest extends TestCase
{
    private MessageRendererInterface $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $factory = new MessageRendererFactory();

        $this->renderer = ($factory)();

        $this
            ->renderer
            ->withVars([
                'hello' => 'Привет',
                'word_limit' => 'трёх слов',
            ])
            ->withHandlers([
                'cmd' => function (string $text) {
                    $commands = Classes::getPublicConstants(Command::class);

                    $commandName = mb_strtoupper($text);
                    $commandText = $commands[$commandName] ?? $text;

                    return '«' . $commandText . '»';
                },
                'q' => fn (string $text) => '«' . $text . '»',
            ]);
    }

    protected function tearDown(): void
    {
        unset($this->renderer);

        parent::tearDown();
    }

    public function testPlainText(): void
    {
        $text = 'just text';

        $this->assertEquals(
            $text,
            $this->renderer->render($text)
        );
    }

    public function testGenderedText(): void
    {
        $text = 'hello, {male|female} friend';

        $this->assertEquals(
            'hello, male friend',
            $this->renderer->render($text)
        );

        $this->assertEquals(
            'hello, female friend',
            $this->renderer->withGender(Gender::FEM)->render($text)
        );
    }

    public function testValidVarAndSemiEmptyGender(): void
    {
        $text = '{hello}, приятель{|ница}!';

        $this->assertEquals(
            'Привет, приятель!',
            $this->renderer->render($text)
        );

        $this->assertEquals(
            'Привет, приятельница!',
            $this->renderer->withGender(Gender::FEM)->render($text)
        );
    }

    public function testInvalidVar(): void
    {
        $text = 'Здоровье: {hp}';

        $this->assertEquals(
            'Здоровье: hp',
            $this->renderer->render($text)
        );
    }
}
