<?php

namespace tests\unit;

use \atoum;

class Entry extends atoum
{
    public function testGetId()
    {
        $entry = new Poche\Model\Entry();

        $this
            ->integer($entry->getId())
            ->isEqualTo('Test')
            ;
    }
}
