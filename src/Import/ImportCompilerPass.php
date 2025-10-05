<?php

namespace Wallabag\Import;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ImportCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ImportChain::class)) {
            return;
        }

        $definition = $container->getDefinition(
            ImportChain::class
        );

        $taggedServices = $container->findTaggedServiceIds(
            'wallabag.import'
        );
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addImport',
                    [new Reference($id), $attributes['alias']]
                );
            }
        }
    }
}
