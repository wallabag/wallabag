<?php

namespace Wallabag\Tests\Functional;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReducedImportersKernel extends \AppKernel
{
    public function getCacheDir(): string
    {
        return \dirname(__DIR__, 2) . '/var/cache/test_reduced_importers';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(static function (ContainerBuilder $container): void {
            $container->setParameter('wallabag.disabled_importers', ['pinboard', 'delicious', 'pocket_csv']);
        });
    }
}
