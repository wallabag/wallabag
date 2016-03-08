<?php

namespace Wallabag\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
                ->integerNode('items_on_page')
                    ->defaultValue(12)
                ->end()
                ->scalarNode('theme')
                    ->defaultValue('material')
                ->end()
                ->scalarNode('language')
                    ->defaultValue('en')
                ->end()
                ->integerNode('rss_limit')
                    ->defaultValue(50)
                ->end()
                ->integerNode('reading_speed')
                    ->defaultValue(200)
                ->end()
                ->scalarNode('version')
                ->end()
                ->scalarNode('paypal_url')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
