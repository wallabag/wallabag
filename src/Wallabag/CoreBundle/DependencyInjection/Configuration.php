<?php

namespace Wallabag\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('wallabag_core');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('languages')
                    ->prototype('scalar')->end()
                ->end()
                ->integerNode('items_on_page')
                    ->defaultValue(12)
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
                ->integerNode('cache_lifetime')
                    ->defaultValue(10)
                ->end()
                ->scalarNode('fetching_error_message')
                ->end()
                ->scalarNode('fetching_error_message_title')
                ->end()
                ->scalarNode('action_mark_as_read')
                    ->defaultValue(1)
                ->end()
                ->scalarNode('list_mode')
                    ->defaultValue(1)
                ->end()
                ->scalarNode('display_thumbnails')
                    ->defaultValue(1)
                ->end()
                ->scalarNode('api_limit_mass_actions')
                    ->defaultValue(10)
                ->end()
                ->arrayNode('default_internal_settings')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('value')->end()
                            ->enumNode('section')
                                ->values(['entry', 'misc', 'api', 'analytics', 'export', 'import'])
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('encryption_key_path')
                ->end()
                ->arrayNode('default_ignore_origin_instance_rules')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('rule')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('fonts')
                    ->prototype('scalar')->end()
                ->end()
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
