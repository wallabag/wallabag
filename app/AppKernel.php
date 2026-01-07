<?php

use BabDev\PagerfantaBundle\BabDevPagerfantaBundle;
use Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle;
use Craue\ConfigBundle\CraueConfigBundle;
use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use FOS\JsRoutingBundle\FOSJsRoutingBundle;
use FOS\OAuthServerBundle\FOSOAuthServerBundle;
use FOS\RestBundle\FOSRestBundle;
use FOS\UserBundle\FOSUserBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use KPhoen\RulerZBundle\KPhoenRulerZBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Nelmio\CorsBundle\NelmioCorsBundle;
use OldSound\RabbitMqBundle\OldSoundRabbitMqBundle;
use Scheb\TwoFactorBundle\SchebTwoFactorBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Sentry\SentryBundle\SentryBundle;
use Spiriit\Bundle\FormFilterBundle\SpiriitFormFilterBundle;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Bundle\WebServerBundle\WebServerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;
use Wallabag\Import\ImportCompilerPass;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new SecurityBundle(),
            new TwigBundle(),
            new MonologBundle(),
            new DoctrineBundle(),
            new SensioFrameworkExtraBundle(),
            new FOSRestBundle(),
            new FOSUserBundle(),
            new JMSSerializerBundle(),
            new NelmioApiDocBundle(),
            new NelmioCorsBundle(),
            new BazingaHateoasBundle(),
            new SpiriitFormFilterBundle(),
            new FOSOAuthServerBundle(),
            new StofDoctrineExtensionsBundle(),
            new SchebTwoFactorBundle(),
            new KPhoenRulerZBundle(),
            new DoctrineMigrationsBundle(),
            new CraueConfigBundle(),
            new BabDevPagerfantaBundle(),
            new FOSJsRoutingBundle(),
            new OldSoundRabbitMqBundle(),
            new SentryBundle(),
            new TwigExtraBundle(),
            new WebpackEncoreBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new DebugBundle();
            $bundles[] = new WebProfilerBundle();
            $bundles[] = new DoctrineFixturesBundle();

            if ('test' === $this->getEnvironment()) {
                $bundles[] = new DAMADoctrineTestBundle();
            }

            if ('dev' === $this->getEnvironment()) {
                $bundles[] = new MakerBundle();
                $bundles[] = new WebServerBundle();
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

        $loader->load(function (ContainerBuilder $container): void {
            // $container->setParameter('container.autowiring.strict_mode', true);
            // $container->setParameter('container.dumper.inline_class_loader', true);
            $container->addObjectResource($this);
        });

        $loader->load(function (ContainerBuilder $container): void {
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
        $scheme = match ($container->getParameter('database_driver')) {
            'pdo_mysql' => 'mysql',
            'pdo_pgsql' => 'pgsql',
            'pdo_sqlite' => 'sqlite',
            default => throw new RuntimeException('Unsupported database driver: ' . $container->getParameter('database_driver')),
        };

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
