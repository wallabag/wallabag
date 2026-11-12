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
            // The SERIAL sequence is named after the prefixed table, or gets a suffix when there is no prefix
            $sequence = $this->connection->fetchOne('SELECT pg_get_serial_sequence(?, ?)', [$this->getTable($table, true), 'id']);

            if (null === $sequence || false === $sequence || $table . '_id_seq' === substr($sequence, strrpos($sequence, '.') + 1)) {
                continue;
            }

            $this->addSql('DROP SEQUENCE IF EXISTS ' . $sequence);
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('The orphan sequences have been deleted.');
    }
}
