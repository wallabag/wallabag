<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Convert the remaining utf8mb3 tables to utf8mb4 (MySQL only, see #9161).
 */
final class Version20261112090000 extends WallabagMigration
{
    private const TABLES = [
        'config',
        'entry_tag',
        'internal_setting',
        'oauth2_access_tokens',
        'oauth2_auth_codes',
        'oauth2_clients',
        'oauth2_refresh_tokens',
        'tagging_rule',
    ];

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->connection->getDatabasePlatform() instanceof MySQLPlatform, 'This migration only apply to MySQL');

        foreach (self::TABLES as $table) {
            $this->addSql('ALTER TABLE ' . $this->getTable($table) . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        }
    }

    public function down(Schema $schema): void
    {
        // Converting back to utf8mb3 would break rows that contain 4-byte characters
    }
}
