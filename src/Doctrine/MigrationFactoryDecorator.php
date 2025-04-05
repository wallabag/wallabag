<?php

namespace Wallabag\Doctrine;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;

/**
 * Decorates the migration factory to pass some additional information to the migration instances.
 */
class MigrationFactoryDecorator implements MigrationFactory
{
    public function __construct(
        private readonly MigrationFactory $migrationFactory,
        private readonly string $tablePrefix,
        private readonly array $defaultIgnoreOriginInstanceRules,
        private readonly string $fetchingErrorMessage,
    ) {
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
