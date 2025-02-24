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
        if (file_exists($this->getProjectDir() . '/app/config/parameters.yml')) {
            $loader->load($this->getProjectDir() . '/app/config/parameters.yml');

            @trigger_error('The "app/config/parameters.yml" file is deprecated and will not be supported in a future version. Move your configuration to environment variables and remove the file.', \E_USER_DEPRECATED);
        }

        $loader->load($this->getProjectDir() . '/app/config/config_' . $this->getEnvironment() . '.yml');

        $loader->load(function (ContainerBuilder $container) {
            // $container->setParameter('container.autowiring.strict_mode', true);
            // $container->setParameter('container.dumper.inline_class_loader', true);
            $container->addObjectResource($this);
        });

        if (file_exists($this->getProjectDir() . '/app/config/parameters.yml')) {
            $loader->load(function (ContainerBuilder $container) {
                $this->loadEnvVarsFromParameters($container);
                $this->defineDatabaseUrlEnvVar($container);
            });
        }
    }

    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ImportCompilerPass());
    }

    private function loadEnvVarsFromParameters(ContainerBuilder $container)
    {
        $this->setEnvVarFromParameter($container, 'DATABASE_TABLE_PREFIX', 'database_table_prefix');

        $this->setEnvVarFromParameter($container, 'DOMAIN_NAME', 'domain_name');
        $this->setEnvVarFromParameter($container, 'SERVER_NAME', 'server_name');
        $this->setEnvVarFromParameter($container, 'MAILER_DSN', 'mailer_dsn');
        $this->setEnvVarFromParameter($container, 'LOCALE', 'locale');
        $this->setEnvVarFromParameter($container, 'SECRET', 'secret');

        $this->setEnvVarFromParameter($container, 'TWOFACTOR_SENDER', 'twofactor_sender');
        $this->setEnvVarFromParameter($container, 'FOSUSER_REGISTRATION', 'fosuser_registration');
        $this->setEnvVarFromParameter($container, 'FOSUSER_CONFIRMATION', 'fosuser_confirmation');
        $this->setEnvVarFromParameter($container, 'FOS_OAUTH_SERVER_ACCESS_TOKEN_LIFETIME', 'fos_oauth_server_access_token_lifetime');
        $this->setEnvVarFromParameter($container, 'FOS_OAUTH_SERVER_REFRESH_TOKEN_LIFETIME', 'fos_oauth_server_refresh_token_lifetime');
        $this->setEnvVarFromParameter($container, 'FROM_EMAIL', 'from_email');

        $this->setEnvVarFromParameter($container, 'RABBITMQ_HOST', 'rabbitmq_host');
        $this->setEnvVarFromParameter($container, 'RABBITMQ_PORT', 'rabbitmq_port');
        $this->setEnvVarFromParameter($container, 'RABBITMQ_USER', 'rabbitmq_user');
        $this->setEnvVarFromParameter($container, 'RABBITMQ_PASSWORD', 'rabbitmq_password');
        $this->setEnvVarFromParameter($container, 'RABBITMQ_PREFETCH_COUNT', 'rabbitmq_prefetch_count');

        $this->setEnvVarFromParameter($container, 'REDIS_SCHEME', 'redis_scheme');
        $this->setEnvVarFromParameter($container, 'REDIS_HOST', 'redis_host');
        $this->setEnvVarFromParameter($container, 'REDIS_PORT', 'redis_port');
        $this->setEnvVarFromParameter($container, 'REDIS_PATH', 'redis_path');
        $this->setEnvVarFromParameter($container, 'REDIS_PASSWORD', 'redis_password');

        $this->setEnvVarFromParameter($container, 'SENTRY_DSN', 'sentry_dsn');
    }

    private function setEnvVarFromParameter(ContainerBuilder $container, string $envVar, string $parameter)
    {
        $_ENV[$envVar] = $_SERVER[$envVar] = (string) $container->getParameter($parameter);
        $container->setParameter('env(' . $envVar . ')', (string) $container->getParameter($parameter));
    }

    private function defineDatabaseUrlEnvVar(ContainerBuilder $container)
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

        $user = $container->getParameter('database_user');
        $password = $container->getParameter('database_password');
        $host = $container->getParameter('database_host');
        $port = $container->getParameter('database_port');
        $name = $container->getParameter('database_name');

        if ('sqlite' === $scheme) {
            $name = $container->getParameter('database_path');
        }

        $url = $scheme . '://' . $user . ':' . $password . '@' . $host;

        if ($port) {
            $url .= ':' . $port;
        }

        $url .= '/' . $name;

        $query = [];

        if ($container->getParameter('database_socket')) {
            $query['unix_socket'] = $container->getParameter('database_socket');
        }

        if ($container->getParameter('database_charset')) {
            $query['charset'] = $container->getParameter('database_charset');
        }

        if ([] !== $query) {
            $url .= '?' . http_build_query($query);
        }

        $_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'] = $url;
        $container->setParameter('env(DATABASE_URL)', $url);
    }
}
