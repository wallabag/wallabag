<?php

namespace Wallabag\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wallabag_core');

        $rootNode
            ->children()
                ->arrayNode('languages')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('import')
                    ->append($this->getAllowMimetypes())
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function getAllowMimetypes()
    {
        $node = new ArrayNodeDefinition('allow_mimetypes');

        $node->prototype('scalar')->end();

        return $node;
    }
}
