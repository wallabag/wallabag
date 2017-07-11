<?php

namespace Wallabag\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class WallabagUserExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $ldap = $container->getParameter('ldap_enabled');

        if ($ldap) {
            $container->prependExtensionConfig('security', array(
              'providers' => array(
                'chain_provider' => array(),
              ),
            ));
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('ldap.yml');
        } elseif ($container->hasExtension('fr3d_ldap')) {
            $container->prependExtensionConfig('fr3_d_ldap', array(
            'driver' => array(
              'host' => 'localhost',
            ),
            'user' => array(
              'baseDn' => 'dc=example,dc=com',
            ),
          ));
        }
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        if ($container->getParameter('ldap_enabled')) {
            $loader->load('ldap_services.yml');
        }
        $container->setParameter('wallabag_user.registration_enabled', $config['registration_enabled']);
    }

    public function getAlias()
    {
        return 'wallabag_user';
    }
}
