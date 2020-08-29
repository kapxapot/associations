<?php

namespace Brightwood\Tests;

use App\Models\TelegramUser;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Data\WoodData;
use Brightwood\Parsing\StoryParser;
use PHPUnit\Framework\TestCase;
use Plasticode\Util\Cases;

final class StoryParserTest extends TestCase
{
    private StoryParser $parser;

    private TelegramUser $default;
    private TelegramUser $male;
    private TelegramUser $female;

    protected function setUp() : void
    {
        parent::setUp();

        $this->parser = new StoryParser();

        $this->default = new TelegramUser();

        $this->male = new TelegramUser(
            [
                'gender_id' => Cases::MAS
            ]
        );

        $this->female = new TelegramUser(
            [
                'gender_id' => Cases::FEM
            ]
        );
    }

    protected function tearDown() : void
    {
        unset($this->female);
        unset($this->male);
        unset($this->default);

        unset($this->parser);

        parent::tearDown();
    }

    public function testPlainText() : void
    {
        $text = 'just text';

        $this->assertEquals(
            $text,
            $this->parser->parse($this->default, $text)
        );
    }

    public function testGenderedText() : void
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

    public function testValidVar() : void
    {
        $text = 'День: {day}, Здоровье: {hp}';

        $data = new StoryData();
        $data->day = 1;
        $data->hp = 10;

        $this->assertEquals(
            'День: 1, Здоровье: 10',
            $this->parser->parse($this->default, $text, $data)
        );
    }

    public function testInvalidVar() : void
    {
        $text = 'День: {day}, Здоровье: {hp}';

        $data = new StoryData();

        $this->assertEquals(
            'День: day, Здоровье: hp',
            $this->parser->parse($this->default, $text, $data)
        );
    }
}
