<?php

namespace Wallabag\CoreBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wallabag\CoreBundle\DependencyInjection\CompilerPass\RegisterWallabagGuzzleSubscribersPass;
use Wallabag\CoreBundle\Import\ImportCompilerPass;

class WallabagCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ImportCompilerPass());
        $container->addCompilerPass(new RegisterWallabagGuzzleSubscribersPass());
    }
}
