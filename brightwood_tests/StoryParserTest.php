<?php

namespace Brightwood\Tests;

use App\Models\Language;
use App\Models\TelegramUser;
use Brightwood\Parsing\StoryParser;
use Brightwood\Parsing\StoryParserFactory;
use Brightwood\Testing\Factories\TranslatorTestFactory;
use Brightwood\Testing\Mocks\DictionaryMock;
use Brightwood\Testing\Models\TestData;
use PHPUnit\Framework\TestCase;
use Plasticode\Semantics\Gender;

final class StoryParserTest extends TestCase
{
    private StoryParser $parser;

    private TelegramUser $default;
    private TelegramUser $male;
    private TelegramUser $female;

    protected function setUp(): void
    {
        parent::setUp();

        $parserFactory = new StoryParserFactory(
            new TranslatorTestFactory([
                Language::RU => new DictionaryMock([
                    'two' => 'два',
                    'two {day}' => 'два {day}'
                ])
            ])
        );

        $this->parser = ($parserFactory)();

        $this->default = new TelegramUser(); // mas, ru

        $this->male = new TelegramUser([
            'gender_id' => Gender::MAS,
            'lang_code' => Language::EN
        ]);

        $this->female = new TelegramUser([
            'gender_id' => Gender::FEM
        ]);
    }

    protected function tearDown(): void
    {
        unset($this->female);
        unset($this->male);
        unset($this->default);

        unset($this->parser);

        parent::tearDown();
    }

    public function testPlainText(): void
    {
        $text = 'just text';

        $this->assertEquals(
            $text,
            $this->parser->parse($this->default, $text)
        );
    }

    public function testGenderedText(): void
    {
        $text = 'hello, {male|female} friend';

        $this->assertEquals(
            'hello, male friend',
            $this->parser->parse($this->male, $text)
        );

        $this->assertEquals(
            'hello, female friend',
            $this->parser->parse($this->female, $text)
        );
    }

    public function testValidVar(): void
    {
        $text = 'День: {day}';

        $data = new TestData();

        $this->assertEquals(
            'День: 1',
            $this->parser->parse($this->default, $text, $data)
        );
    }

    public function testInvalidVar(): void
    {
        $text = 'Здоровье: {hp}';

        $data = new TestData();

        $this->assertEquals(
            'Здоровье: hp',
            $this->parser->parse($this->default, $text, $data)
        );
    }

    public function testTranslateSimple(): void
    {
        $text = 'one [[two]] three';

        $this->assertEquals(
            'one два three',
            $this->parser->parse($this->default, $text)
        );

        $this->assertEquals(
            'one two three',
            $this->parser->parse($this->male, $text)
        );
    }

    public function testTranslateWithVar(): void
    {
        $text = 'one [[two {day}]] three';

        $data = new TestData();

        $this->assertEquals(
            'one два 1 three',
            $this->parser->parse($this->default, $text, $data)
        );

        $this->assertEquals(
            'one two 1 three',
            $this->parser->parse($this->male, $text, $data)
        );
    }

    public function testTranslateUndefined(): void
    {
        $text = 'one two [[three]]';

        $this->assertEquals(
            'one two three',
            $this->parser->parse($this->default, $text)
        );

        $this->assertEquals(
            'one two three',
            $this->parser->parse($this->male, $text)
        );
    }
}
