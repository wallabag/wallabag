<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Converted database to utf8mb4 encoding (for MySQL only).
 */
class Version20161022134138 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->write('This migration only apply to MySQL');

            return;
        }

        $this->addSql('ALTER DATABASE `' . $this->connection->getParams()['dbname'] . '` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;');

        // convert field length for utf8mb4
        // http://stackoverflow.com/a/31474509/569101
        $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL;');
        $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE salt salt VARCHAR(180) NOT NULL;');
        $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE password password VARCHAR(180) NOT NULL;');

        $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');

        $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' CHANGE `text` `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' CHANGE `quote` `quote` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');

        $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE `title` `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE `content` `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');

        $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CHANGE `label` `label` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');

        $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE `name` `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            $this->write('This migration only apply to MySQL');

            return;
        }

        $this->addSql('ALTER DATABASE `' . $this->connection->getParams()['dbname'] . '` CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;');

        $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');

        $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' CHANGE `text` `text` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('annotation') . ' CHANGE `quote` `quote` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;');

        $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE `title` `title` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->addSql('ALTER TABLE ' . $this->getTable('entry') . ' CHANGE `content` `content` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;');

        $this->addSql('ALTER TABLE ' . $this->getTable('tag') . ' CHANGE `label` `label` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;');

        $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE `name` `name` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
    }
}
