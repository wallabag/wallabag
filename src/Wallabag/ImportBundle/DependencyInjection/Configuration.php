<?php

namespace Wallabag\ImportBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('wallabag_import');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('allow_mimetypes')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('resource_dir')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
