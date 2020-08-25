<?php

namespace Brightwood\Tests;

use App\Models\TelegramUser;
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
            $this->parser->parseFor($this->default, $text)
        );
    }
}
