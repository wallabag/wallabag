<?php

namespace Wallabag\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WallabagCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('wallabag_core.languages', $config['languages']);
        $container->setParameter('wallabag_core.items_on_page', $config['items_on_page']);
        $container->setParameter('wallabag_core.theme', $config['theme']);
        $container->setParameter('wallabag_core.language', $config['language']);
        $container->setParameter('wallabag_core.feed_limit', $config['rss_limit']);
        $container->setParameter('wallabag_core.reading_speed', $config['reading_speed']);
        $container->setParameter('wallabag_core.version', $config['version']);
        $container->setParameter('wallabag_core.paypal_url', $config['paypal_url']);
        $container->setParameter('wallabag_core.cache_lifetime', $config['cache_lifetime']);
        $container->setParameter('wallabag_core.action_mark_as_read', $config['action_mark_as_read']);
        $container->setParameter('wallabag_core.list_mode', $config['list_mode']);
        $container->setParameter('wallabag_core.fetching_error_message', $config['fetching_error_message']);
        $container->setParameter('wallabag_core.fetching_error_message_title', $config['fetching_error_message_title']);
        $container->setParameter('wallabag_core.api_limit_mass_actions', $config['api_limit_mass_actions']);
        $container->setParameter('wallabag_core.default_internal_settings', $config['default_internal_settings']);
        $container->setParameter('wallabag_core.site_credentials.encryption_key_path', $config['encryption_key_path']);
        $container->setParameter('wallabag_core.default_ignore_origin_instance_rules', $config['default_ignore_origin_instance_rules']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('parameters.yml');
    }

    public function getAlias()
    {
        return 'wallabag_core';
    }
}
