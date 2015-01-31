<?php

namespace Wallabag\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wallabag\CoreBundle\DependencyInjection\Security\Factory\WsseFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WallabagCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new WsseFactory());
    }
}
