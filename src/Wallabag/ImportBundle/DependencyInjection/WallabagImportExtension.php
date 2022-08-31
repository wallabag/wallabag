<?php

namespace Wallabag\ImportBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WallabagImportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('wallabag_import.allow_mimetypes', $config['allow_mimetypes']);
        $container->setParameter('wallabag_import.resource_dir', $config['resource_dir']);
    }

    public function getAlias()
    {
        return 'wallabag_import';
    }
}
