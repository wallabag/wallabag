<?php

namespace Wallabag\ImportBundle\Import;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ImportCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('Wallabag\ImportBundle\Import\ImportChain')) {
            return;
        }

        $definition = $container->getDefinition(
            'Wallabag\ImportBundle\Import\ImportChain'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'wallabag_import.import'
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
