<?php

namespace App\Tests\Bots;

use App\Bots\Factories\BotMessageRendererFactory;
use App\Bots\Interfaces\MessageRendererInterface;
use PHPUnit\Framework\TestCase;
use Plasticode\Semantics\Gender;

final class MessageRendererTest extends TestCase
{
    private MessageRendererInterface $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $factory = new BotMessageRendererFactory();

        $this->renderer = ($factory)();
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

        $this->renderer->withVar('hello', 'Привет');

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

    public function testQuoteHandler(): void
    {
        $text = '{q:ёлка}';

        $this->assertEquals(
            '«ёлка»',
            $this->renderer->render($text)
        );
    }

    public function testCommandHandler(): void
    {
        $text = '{cmd:exit}';

        $this->assertEquals(
            '«хватит»',
            $this->renderer->render($text)
        );
    }

    public function testUnknownCommandHandler(): void
    {
        $text = '{cmd:bark}';

        $this->assertEquals(
            '«bark»',
            $this->renderer->render($text)
        );
    }

    public function testAttitudeVar(): void
    {
        $text = '{att:Здравствуйте|Привет}, {att:уважаемый|чувак}!';

        $this->assertEquals(
            'Здравствуйте, уважаемый!',
            $this->renderer->render($text)
        );

        $this->assertEquals(
            'Привет, чувак!',
            $this->renderer->withVar('att', 2)->render($text)
        );
    }
}
