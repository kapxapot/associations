<?php

namespace Brightwood\Tests\Models;

use Brightwood\Models\Links\ActionLink;
use Brightwood\Testing\Models\TestData;
use Brightwood\Testing\Models\TestStory;
use PHPUnit\Framework\TestCase;

final class ActionLinkTest extends TestCase
{
    public function testEmptyMutatePreservesData() : void
    {
        $story = new TestStory(1);
        $data = $story->makeData();

        $this->assertNotNull($data);

        $link = new ActionLink(0, 'some action');

        $resultData = $link->mutate($data);

        $this->assertNotNull($resultData);
        $this->assertEquals($data->toArray(), $resultData->toArray());
    }

    public function testMutateMutatesData() : void
    {
        $story = new TestStory(1);
        $data = $story->makeData();

        $this->assertNotNull($data);

        $link = (new ActionLink(0, 'some action'))
            ->does(
                fn (TestData $d) => $d->nextDay()
            );

        $day = $data->day;

        /** @var TestData */
        $resultData = $link->mutate($data);

        $this->assertNotNull($resultData);
        $this->assertEquals($day + 1, $resultData->day);
    }
}
