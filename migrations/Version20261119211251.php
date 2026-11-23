<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20261119211251 extends WallabagMigration
{
    public function getDescription(): string
    {
        return 'Allow automatic imports for feed subscriptions';
    }

    public function up(Schema $schema): void
    {
        $table = $this->getTable('feed_url');
        $definition = match (true) {
            $this->platform instanceof MySQLPlatform => 'TINYINT(1) DEFAULT 0 NOT NULL',
            $this->platform instanceof PostgreSQLPlatform => 'BOOLEAN DEFAULT false NOT NULL',
            $this->platform instanceof SqlitePlatform => 'BOOLEAN DEFAULT 0 NOT NULL',
            default => throw new \RuntimeException('Unsupported database platform.'),
        };

        $this->addSql("ALTER TABLE $table ADD COLUMN automatic $definition");
    }

    public function down(Schema $schema): void
    {
        $table = $this->getTable('feed_url');

        switch (true) {
            case $this->platform instanceof MySQLPlatform:
            case $this->platform instanceof PostgreSQLPlatform:
                $this->addSql("ALTER TABLE $table DROP COLUMN automatic");
                break;
            case $this->platform instanceof SqlitePlatform:
                $this->dropSqliteAutomatic($table);
                break;
            default:
                $this->abortIf(true, 'Unsupported database platform.');
        }
    }

    private function dropSqliteAutomatic(string $table): void
    {
        if (version_compare((string) $this->connection->fetchOne('SELECT sqlite_version()'), '3.35.5', '>=')) {
            $this->addSql("ALTER TABLE $table DROP COLUMN automatic");

            return;
        }

        $backup = $this->connection->quoteIdentifier('__temp__' . $this->getTable('feed_url', true));
        $user = $this->getTable('user');
        $index = $this->getIndexName('feed_url', 'user_id');
        $foreignKey = $this->getForeignKeyName('feed_url', 'user_id');

        // Rebuilding the parent table must not cascade into the article history.
        $restoreForeignKeys = (bool) $this->connection->fetchOne('PRAGMA foreign_keys');

        if ($restoreForeignKeys) {
            $this->addSql('PRAGMA foreign_keys = OFF');
        }

        $this->addSql("CREATE TEMPORARY TABLE $backup AS SELECT id, user_id, title, url, updated_at FROM $table");
        $this->addSql("DROP TABLE $table");
        $this->addSql("CREATE TABLE $table (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, title CLOB NOT NULL, url CLOB NOT NULL, updated_at DATETIME DEFAULT NULL, CONSTRAINT $foreignKey FOREIGN KEY (user_id) REFERENCES $user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)");
        $this->addSql("INSERT INTO $table (id, user_id, title, url, updated_at) SELECT id, user_id, title, url, updated_at FROM $backup");
        $this->addSql("DROP TABLE $backup");
        $this->addSql("CREATE INDEX $index ON $table (user_id)");

        if ($restoreForeignKeys) {
            $this->addSql('PRAGMA foreign_keys = ON');
        }
    }
}
