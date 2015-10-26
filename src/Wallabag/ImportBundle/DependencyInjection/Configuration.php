<?php

namespace Wallabag\ImportBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wallabag_import');

        $rootNode
            ->children()
                ->arrayNode('importers')
                    ->append($this->getURLs())
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function getURLs()
    {
        $node = new ArrayNodeDefinition('pocket_urls');
        $node->prototype('scalar')->end();

        return $node;
    }
}
