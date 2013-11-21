<?php

namespace Poche\Util\tests\units;

use \atoum;

class Token extends atoum
{
    public function testTokenSize()
    {
        $this
            ->integer(strlen(\Poche\Util\Token::generateToken()))
            ->isEqualTo(15)
            ;
    }
}

