<?php

namespace Wallabag\CoreBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Wallabag\CoreBundle\Guzzle\FixupMondeDiplomatiqueUriSubscriber;
use Wallabag\CoreBundle\Helper\HttpClientFactory;

class RegisterWallabagGuzzleSubscribersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(HttpClientFactory::class);

        // manually add subscribers for some websites
        $definition->addMethodCall(
            'addSubscriber', [
                new Reference(FixupMondeDiplomatiqueUriSubscriber::class),
            ]
        );
    }
}
