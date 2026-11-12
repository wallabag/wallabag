<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Drop the sequences left by the former SERIAL id columns (PostgreSQL only).
 *
 * Since Version20240310150000, these ids use the unprefixed sequences managed by Doctrine.
 */
final class Version20261112100000 extends WallabagMigration
{
    private const TABLES = [
        'ignore_origin_instance_rule',
        'ignore_origin_user_rule',
        'site_credential',
    ];

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'This migration only apply to PostgreSQL');

        foreach (self::TABLES as $table) {
            $this->addSql('DROP SEQUENCE IF EXISTS ' . $this->getTable($table . '_id_seq', true));
        }
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'This migration only apply to PostgreSQL');

        foreach (self::TABLES as $table) {
            $this->addSql('CREATE SEQUENCE IF NOT EXISTS ' . $this->getTable($table . '_id_seq', true));
        }
    }
}
