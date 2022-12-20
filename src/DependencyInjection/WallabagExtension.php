<?php

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WallabagExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('wallabag.languages', $config['languages']);
        $container->setParameter('wallabag.items_on_page', $config['items_on_page']);
        $container->setParameter('wallabag.language', $config['language']);
        $container->setParameter('wallabag.feed_limit', $config['rss_limit']);
        $container->setParameter('wallabag.reading_speed', $config['reading_speed']);
        $container->setParameter('wallabag.version', $config['version']);
        $container->setParameter('wallabag.paypal_url', $config['paypal_url']);
        $container->setParameter('wallabag.cache_lifetime', $config['cache_lifetime']);
        $container->setParameter('wallabag.action_mark_as_read', $config['action_mark_as_read']);
        $container->setParameter('wallabag.list_mode', $config['list_mode']);
        $container->setParameter('wallabag.fetching_error_message', $config['fetching_error_message']);
        $container->setParameter('wallabag.fetching_error_message_title', $config['fetching_error_message_title']);
        $container->setParameter('wallabag.api_limit_mass_actions', $config['api_limit_mass_actions']);
        $container->setParameter('wallabag.default_internal_settings', $config['default_internal_settings']);
        $container->setParameter('wallabag.site_credentials.encryption_key_path', $config['encryption_key_path']);
        $container->setParameter('wallabag.default_ignore_origin_instance_rules', $config['default_ignore_origin_instance_rules']);

        $container->setParameter('wallabag.import.allow_mimetypes', $config['import']['allow_mimetypes']);
        $container->setParameter('wallabag.import.resource_dir', $config['import']['resource_dir']);
    }

    public function getAlias()
    {
        return 'wallabag';
    }
}
