<?php

namespace Poche\Model\tests\units;

use \atoum;

class Entry extends atoum
{
    public function testGetId()
    {
        $entry = new \Poche\Model\Entry(1, "Titre test");

        $this
            ->integer($entry->getId())
            ->isEqualTo(1)
            ;
    }
}
