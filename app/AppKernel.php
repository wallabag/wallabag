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
        $legacyParametersLoaded = $this->loadLegacyParametersIfPresent($loader);

        $loader->load(function (ContainerBuilder $container): void {
            // $container->setParameter('container.autowiring.strict_mode', true);
            // $container->setParameter('container.dumper.inline_class_loader', true);
            $container->addObjectResource($this);
        });

        $loader->load(function (ContainerBuilder $container) use ($legacyParametersLoaded): void {
            if (!$legacyParametersLoaded) {
                return;
            }

            $this->triggerLegacyParametersDeprecationIfNeeded();
            $this->defineLegacyEnvFallbacks($container);
        });

        $loader->load($this->getProjectDir() . '/app/config/config_' . $this->getEnvironment() . '.yml');

        if ('dev' === $this->getEnvironment()) {
            $loader->load($this->getProjectDir() . '/app/config/services_dev.yml');
        }
    }

    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ImportCompilerPass());
    }

    private function triggerLegacyParametersDeprecationIfNeeded(): void
    {
        trigger_deprecation(
            'wallabag/wallabag',
            '2.x',
            'Loading configuration from "app/config/parameters.yml" is deprecated and will be removed in wallabag 3.0. Configure wallabag with environment variables instead.'
        );
    }

    private function loadLegacyParametersIfPresent(LoaderInterface $loader): bool
    {
        $legacyParametersPath = $this->getProjectDir() . '/app/config/parameters.yml';

        if (!is_file($legacyParametersPath)) {
            return false;
        }

        $loader->load($legacyParametersPath);

        return true;
    }

    private function defineLegacyEnvFallbacks(ContainerBuilder $container): void
    {
        $this->defineLegacySimpleEnvFallbacks($container);
        $this->defineLegacyDatabaseUrlFallback($container);
        $this->defineLegacyRedisUrlFallback($container);
        $this->defineLegacyRabbitMqUrlFallback($container);
    }

    private function defineLegacySimpleEnvFallbacks(ContainerBuilder $container): void
    {
        if ($container->hasParameter('secret')) {
            $container->setParameter('env(APP_SECRET)', (string) $container->getParameter('secret'));
        }

        if ($container->hasParameter('locale')) {
            $container->setParameter('env(DEFAULT_LOCALE)', (string) $container->getParameter('locale'));
        }

        if ($container->hasParameter('domain_name')) {
            $container->setParameter('env(WALLABAG_BASE_URL)', (string) $container->getParameter('domain_name'));
        }

        if ($container->hasParameter('mailer_dsn')) {
            $container->setParameter('env(MAILER_DSN)', (string) $container->getParameter('mailer_dsn'));
        }

        if ($container->hasParameter('fosuser_registration')) {
            $container->setParameter('env(WALLABAG_REGISTRATION_ENABLED)', (bool) $container->getParameter('fosuser_registration') ? '1' : '0');
        }

        if ($container->hasParameter('fosuser_confirmation')) {
            $container->setParameter('env(WALLABAG_CONFIRMATION_ENABLED)', (bool) $container->getParameter('fosuser_confirmation') ? '1' : '0');
        }

        if ($container->hasParameter('from_email')) {
            $container->setParameter('env(WALLABAG_FROM_EMAIL)', (string) $container->getParameter('from_email'));
        }

        if ($container->hasParameter('server_name')) {
            $container->setParameter('env(WALLABAG_SERVER_NAME)', (string) $container->getParameter('server_name'));
        }

        if ($container->hasParameter('twofactor_sender')) {
            $container->setParameter('env(WALLABAG_TWOFACTOR_SENDER)', (string) $container->getParameter('twofactor_sender'));
        }

        if ($container->hasParameter('rabbitmq_prefetch_count')) {
            $container->setParameter('env(WALLABAG_RABBITMQ_PREFETCH_COUNT)', (string) $container->getParameter('rabbitmq_prefetch_count'));
        }
    }

    private function defineLegacyDatabaseUrlFallback(ContainerBuilder $container): void
    {
        $databaseParameters = $this->normalizeLegacyDatabaseParameters($container);

        if ('sqlite' === $databaseParameters['scheme']) {
            $databasePath = '' !== $databaseParameters['path'] ? $databaseParameters['path'] : $databaseParameters['name'];
            $container->setParameter(
                'env(DATABASE_URL)',
                str_starts_with($databasePath, '/')
                    ? 'sqlite://' . $databasePath
                    : 'sqlite:///' . ltrim($databasePath, '/')
            );

            return;
        }

        $url = $databaseParameters['scheme'] . '://';

        if ('' !== $databaseParameters['user'] || '' !== $databaseParameters['password']) {
            $url .= rawurlencode($databaseParameters['user']);

            if ('' !== $databaseParameters['password']) {
                $url .= ':' . rawurlencode($databaseParameters['password']);
            }

            $url .= '@';
        }

        $url .= $databaseParameters['host'];

        if ('' !== $databaseParameters['port']) {
            $url .= ':' . $databaseParameters['port'];
        }

        $url .= '/' . $databaseParameters['name'];

        $query = [];

        if ('' !== $databaseParameters['socket']) {
            $query['unix_socket'] = $databaseParameters['socket'];
        }

        if ('' !== $databaseParameters['charset']) {
            $query['charset'] = $databaseParameters['charset'];
        }

        if ([] !== $query) {
            $url .= '?' . http_build_query($query, '', '&', \PHP_QUERY_RFC3986);
        }

        $container->setParameter('env(DATABASE_URL)', $url);
    }

    private function normalizeLegacyDatabaseParameters(ContainerBuilder $container): array
    {
        $scheme = match ($container->getParameter('database_driver')) {
            'pdo_mysql' => 'mysql',
            'pdo_pgsql' => 'postgresql',
            'pdo_sqlite' => 'sqlite',
            default => throw new RuntimeException('Unsupported database driver: ' . $container->getParameter('database_driver')),
        };

        return [
            'scheme' => $scheme,
            'host' => (string) $container->getParameter('database_host'),
            'port' => (string) $container->getParameter('database_port'),
            'name' => (string) $container->getParameter('database_name'),
            'user' => (string) $container->getParameter('database_user'),
            'password' => (string) $container->getParameter('database_password'),
            'path' => (string) $container->getParameter('database_path'),
            'socket' => (string) $container->getParameter('database_socket'),
            'charset' => (string) $container->getParameter('database_charset'),
        ];
    }

    private function defineLegacyRedisUrlFallback(ContainerBuilder $container): void
    {
        $scheme = (string) $container->getParameter('redis_scheme');
        $host = (string) $container->getParameter('redis_host');
        $port = (string) $container->getParameter('redis_port');
        $path = (string) $container->getParameter('redis_path');
        $password = (string) $container->getParameter('redis_password');

        if ('unix' === $scheme) {
            $url = 'unix://' . $path;

            if ('' !== $password) {
                $url .= '?password=' . rawurlencode($password);
            }

            $container->setParameter('env(REDIS_URL)', $url);

            return;
        }

        $url = $scheme . '://';

        if ('' !== $password) {
            $url .= ':' . rawurlencode($password) . '@';
        }

        $url .= $host;

        if ('' !== $port) {
            $url .= ':' . $port;
        }

        if ('' !== $path) {
            $url .= '/' . ltrim($path, '/');
        }

        $container->setParameter('env(REDIS_URL)', $url);
    }

    private function defineLegacyRabbitMqUrlFallback(ContainerBuilder $container): void
    {
        $host = (string) $container->getParameter('rabbitmq_host');
        $port = (string) $container->getParameter('rabbitmq_port');
        $user = (string) $container->getParameter('rabbitmq_user');
        $password = (string) $container->getParameter('rabbitmq_password');

        $url = 'amqp://';

        if ('' !== $user || '' !== $password) {
            $url .= rawurlencode($user);

            if ('' !== $password) {
                $url .= ':' . rawurlencode($password);
            }

            $url .= '@';
        }

        $url .= $host;

        if ('' !== $port) {
            $url .= ':' . $port;
        }

        $container->setParameter('env(RABBITMQ_URL)', $url);
    }
}
