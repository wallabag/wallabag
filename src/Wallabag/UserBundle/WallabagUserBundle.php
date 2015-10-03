<?php

namespace Wallabag\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WallabagUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
