<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Restore the API client names stored as hex by a previous version of Version20240310150000 (PostgreSQL only).
 */
final class Version20261112110000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'This migration only apply to PostgreSQL');

        $this->addSql('UPDATE ' . $this->getTable('oauth2_clients') . " SET name = convert_from(decode(substring(name from 3), 'hex'), 'UTF8') WHERE name ~ '^\\\\x([0-9a-f]{2})+$'");
    }

    public function down(Schema $schema): void
    {
        // Nothing to restore
    }
}
