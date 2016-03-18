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
        $container->setParameter('wallabag_core.rss_limit', $config['rss_limit']);
        $container->setParameter('wallabag_core.reading_speed', $config['reading_speed']);
        $container->setParameter('wallabag_core.version', $config['version']);
        $container->setParameter('wallabag_core.paypal_url', $config['paypal_url']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function getAlias()
    {
        return 'wallabag_core';
    }
}
