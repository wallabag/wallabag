<?php

namespace Wallabag\Util\tests\units;

use \atoum;

class Token extends atoum
{
    public function testTokenSize()
    {
        $this
            ->integer(strlen(\Wallabag\Util\Token::generateToken()))
            ->isEqualTo(15)
            ;
    }
}

