<?php

namespace App\Events\DictWord;

/**
 * This event is fired when the dict word is linked with word.
 * 
 * It can happen on dict word creation or later the word wasn't linked
 * initially or is relinked to another word.
 */
class DictWordLinkedEvent extends DictWordEvent
{
}
