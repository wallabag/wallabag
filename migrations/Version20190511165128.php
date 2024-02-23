<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Convert tab label to utf8mb4_bin (MySQL only).
 */
final class Version20190511165128 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->write('This migration only apply to MySQL');

            return;
        }

        $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CHANGE `label` `label` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;');
        $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CHANGE `slug` `slug` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->write('This migration only apply to MySQL');

            return;
        }

        $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CHANGE `slug` `slug` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CHANGE `label` `label` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
    }
}
