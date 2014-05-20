<?php

namespace Wallabag\Model\tests\units;

use \atoum;

class Entry extends atoum
{
    public function testGetId()
    {
        $entry = new \Wallabag\Model\Entry(1, "Titre test");

        $this
            ->integer($entry->getId())
            ->isEqualTo(1)
            ;
    }
}
