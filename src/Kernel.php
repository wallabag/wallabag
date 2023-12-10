<?php

namespace Wallabag;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Wallabag\Import\ImportCompilerPass;

class Kernel extends BaseKernel
{
    use MicroKernelTrait {
        registerContainerConfiguration as registerContainerConfigurationFromMicroKernelTrait;
    }

    public function getLogDir()
    {
        return \dirname(__DIR__) . '/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $this->registerContainerConfigurationFromMicroKernelTrait($loader);

        $loader->load(function (ContainerBuilder $container) {
            $this->processDatabaseParameters($container);
        });
    }

    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ImportCompilerPass());
    }

    private function processDatabaseParameters(ContainerBuilder $container)
    {
        switch ($container->getParameter('database_driver')) {
            case 'pdo_mysql':
                $scheme = 'mysql';
                break;
            case 'pdo_pgsql':
                $scheme = 'pgsql';
                break;
            case 'pdo_sqlite':
                $scheme = 'sqlite';
                break;
            default:
                throw new \RuntimeException('Unsupported database driver: ' . $container->getParameter('database_driver'));
        }

        $container->setParameter('database_scheme', $scheme);

        if ('sqlite' === $scheme) {
            $container->setParameter('database_name', $container->getParameter('database_path'));
        }

        $container->setParameter('database_user', (string) $container->getParameter('database_user'));
        $container->setParameter('database_password', (string) $container->getParameter('database_password'));
        $container->setParameter('database_port', (string) $container->getParameter('database_port'));
        $container->setParameter('database_socket', (string) $container->getParameter('database_socket'));
    }
}
