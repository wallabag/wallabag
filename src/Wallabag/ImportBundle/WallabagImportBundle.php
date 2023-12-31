<?php

namespace Wallabag\ImportBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wallabag\CoreBundle\Import\ImportCompilerPass;

class WallabagImportBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ImportCompilerPass());
    }
}
