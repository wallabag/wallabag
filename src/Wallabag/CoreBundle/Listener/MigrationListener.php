<?php

namespace Wallabag\CoreBundle\Listener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class MigrationListener implements EventSubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $migrationPath;

    /**
     * @var string
     */
    private $migrationNamespace;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var boolean
     */
    private $enabled;

    /**
     * MigrationListener constructor.
     *
     * @param $connection
     * @param $migrationPath
     * @param $migrationNamespace
     * @param $container
     * @param $enabled
     */
    public function __construct($connection, $migrationPath, $migrationNamespace, $container, $enabled)
    {
        $this->connection = $connection;
        $this->migrationPath = $migrationPath;
        $this->migrationNamespace = $migrationNamespace;

        $this->container = $container;
        $this->enabled = $enabled;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'checkMigrations'
        ];
    }

    /**
     * @throws \Doctrine\DBAL\Migrations\MigrationException
     */
    public function checkMigrations()
    {
        if ($this->enabled !== true) {
            return;
        }

        $config = new Configuration($this->connection);#
        $config->setMigrationsDirectory($this->migrationPath);
        $config->setMigrationsNamespace($this->migrationNamespace);

        $config->registerMigrationsFromDirectory($this->migrationPath);
        foreach ($config->getMigrations() as $version) {
            $migration = $version->getMigration();
            if ($migration instanceof ContainerAwareInterface) {
                $migration->setContainer($this->container);
            }
        }

        $migration = new Migration($config);
        $migration->migrate(null, false, false, null);
    }
}