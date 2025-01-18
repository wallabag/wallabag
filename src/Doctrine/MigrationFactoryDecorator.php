<?php

namespace Wallabag\Doctrine;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;

/**
 * Decorates the migration factory to pass some additional information to the migration instances.
 */
class MigrationFactoryDecorator implements MigrationFactory
{
    private MigrationFactory $migrationFactory;
    private string $tablePrefix;
    private array $defaultIgnoreOriginInstanceRules;
    private string $fetchingErrorMessage;

    public function __construct(MigrationFactory $migrationFactory, string $tablePrefix, array $defaultIgnoreOriginInstanceRules, string $fetchingErrorMessage)
    {
        $this->migrationFactory = $migrationFactory;
        $this->tablePrefix = $tablePrefix;
        $this->defaultIgnoreOriginInstanceRules = $defaultIgnoreOriginInstanceRules;
        $this->fetchingErrorMessage = $fetchingErrorMessage;
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $instance = $this->migrationFactory->createVersion($migrationClassName);

        if ($instance instanceof WallabagMigration) {
            $instance->setTablePrefix($this->tablePrefix);
            $instance->setDefaultIgnoreOriginInstanceRules($this->defaultIgnoreOriginInstanceRules);
            $instance->setFetchingErrorMessage($this->fetchingErrorMessage);
        }

        return $instance;
    }
}
