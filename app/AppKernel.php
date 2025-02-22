<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Wallabag\Import\ImportCompilerPass;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
            new Spiriit\Bundle\FormFilterBundle\SpiriitFormFilterBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Scheb\TwoFactorBundle\SchebTwoFactorBundle(),
            new KPhoen\RulerZBundle\KPhoenRulerZBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Craue\ConfigBundle\CraueConfigBundle(),
            new BabDev\PagerfantaBundle\BabDevPagerfantaBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new Sentry\SentryBundle\SentryBundle(),
            new Twig\Extra\TwigExtraBundle\TwigExtraBundle(),
            new Symfony\WebpackEncoreBundle\WebpackEncoreBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();

            if ('test' === $this->getEnvironment()) {
                $bundles[] = new DAMA\DoctrineTestBundle\DAMADoctrineTestBundle();
            }

            if ('dev' === $this->getEnvironment()) {
                $bundles[] = new Symfony\Bundle\MakerBundle\MakerBundle();
                $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
            }
        }

        return $bundles;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getProjectDir() . '/app/config/config_' . $this->getEnvironment() . '.yml');

        $loader->load(function (ContainerBuilder $container) {
            // $container->setParameter('container.autowiring.strict_mode', true);
            // $container->setParameter('container.dumper.inline_class_loader', true);
            $container->addObjectResource($this);
        });

        $loader->load(function (ContainerBuilder $container) {
            $this->processDatabaseParameters($container);
            $this->defineRedisUrlEnvVar($container);
            $this->defineRabbitMqUrlEnvVar($container);
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
                throw new RuntimeException('Unsupported database driver: ' . $container->getParameter('database_driver'));
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

    private function defineRedisUrlEnvVar(ContainerBuilder $container)
    {
        $scheme = $container->getParameter('redis_scheme');
        $host = $container->getParameter('redis_host');
        $port = $container->getParameter('redis_port');
        $path = $container->getParameter('redis_path');
        $password = $container->getParameter('redis_password');

        $url = $scheme . '://';

        if ($password) {
            $url .= $password . '@';
        }

        $url .= $host;

        if ($port) {
            $url .= ':' . $port;
        }

        $url .= '/' . ltrim($path, '/');

        $container->setParameter('env(REDIS_URL)', $url);
    }

    private function defineRabbitMqUrlEnvVar(ContainerBuilder $container)
    {
        $host = $container->getParameter('rabbitmq_host');
        $port = $container->getParameter('rabbitmq_port');
        $user = $container->getParameter('rabbitmq_user');
        $password = $container->getParameter('rabbitmq_password');

        $url = 'amqp://' . $user . ':' . $password . '@' . $host;

        if ($port) {
            $url .= ':' . $port;
        }

        $container->setParameter('env(RABBITMQ_URL)', $url);
    }
}
