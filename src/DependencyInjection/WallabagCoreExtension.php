<?php

namespace Wallabag\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WallabagCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
    }

    public function getAlias()
    {
        return 'wallabag_core';
    }
}
