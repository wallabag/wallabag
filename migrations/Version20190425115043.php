<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Rename rss_token & rss_limit to feed_token & feed_limit.
 */
final class Version20190425115043 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('DROP INDEX UNIQ_87E64C53A76ED395');
                $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('config', true) . ' AS SELECT id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode FROM ' . $this->getTable('config', true));
                $this->addSql('DROP TABLE ' . $this->getTable('config', true));
                $this->addSql('CREATE TABLE ' . $this->getTable('config', true) . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, theme VARCHAR(255) NOT NULL COLLATE BINARY, items_per_page INTEGER NOT NULL, language VARCHAR(255) NOT NULL COLLATE BINARY, reading_speed DOUBLE PRECISION DEFAULT NULL, pocket_consumer_key VARCHAR(255) DEFAULT NULL COLLATE BINARY, action_mark_as_read INTEGER DEFAULT 0, list_mode INTEGER DEFAULT NULL, feed_token VARCHAR(255) DEFAULT NULL, feed_limit INTEGER DEFAULT NULL, CONSTRAINT FK_87E64C53A76ED395 FOREIGN KEY (user_id) REFERENCES "' . $this->getTable('user', true) . '" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO ' . $this->getTable('config', true) . ' (id, user_id, theme, items_per_page, language, feed_token, feed_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode) SELECT id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode FROM __temp__' . $this->getTable('config', true));
                $this->addSql('DROP TABLE __temp__' . $this->getTable('config', true));
                $this->addSql('CREATE UNIQUE INDEX UNIQ_87E64C53A76ED395 ON ' . $this->getTable('config', true) . ' (user_id)');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('config') . ' CHANGE rss_token feed_token VARCHAR(255) DEFAULT NULL');
                $this->addSql('ALTER TABLE ' . $this->getTable('config') . ' CHANGE rss_limit feed_limit INT DEFAULT NULL');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('config') . ' RENAME COLUMN rss_token TO feed_token');
                $this->addSql('ALTER TABLE ' . $this->getTable('config') . ' RENAME COLUMN rss_limit TO feed_limit');
                break;
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('DROP INDEX UNIQ_87E64C53A76ED395');
                $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('config', true) . ' AS SELECT id, user_id, theme, items_per_page, language, feed_token, feed_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode FROM "' . $this->getTable('config', true) . '"');
                $this->addSql('DROP TABLE "' . $this->getTable('config', true) . '"');
                $this->addSql('CREATE TABLE "' . $this->getTable('config', true) . '" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, theme VARCHAR(255) NOT NULL, items_per_page INTEGER NOT NULL, language VARCHAR(255) NOT NULL, reading_speed DOUBLE PRECISION DEFAULT NULL, pocket_consumer_key VARCHAR(255) DEFAULT NULL, action_mark_as_read INTEGER DEFAULT 0, list_mode INTEGER DEFAULT NULL, rss_token VARCHAR(255) DEFAULT NULL COLLATE BINARY, rss_limit INTEGER DEFAULT NULL)');
                $this->addSql('INSERT INTO "' . $this->getTable('config', true) . '" (id, user_id, theme, items_per_page, language, rss_token, rss_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode) SELECT id, user_id, theme, items_per_page, language, feed_token, feed_limit, reading_speed, pocket_consumer_key, action_mark_as_read, list_mode FROM __temp__' . $this->getTable('config', true));
                $this->addSql('DROP TABLE __temp__' . $this->getTable('config', true));
                $this->addSql('CREATE UNIQUE INDEX UNIQ_87E64C53A76ED395 ON "' . $this->getTable('config', true) . '" (user_id)');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('config') . ' CHANGE feed_token rss_token');
                $this->addSql('ALTER TABLE ' . $this->getTable('config') . ' CHANGE feed_limit rss_limit');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('config') . ' RENAME COLUMN feed_token TO rss_token');
                $this->addSql('ALTER TABLE ' . $this->getTable('config') . ' RENAME COLUMN feed_limit TO rss_limit');
                break;
        }
    }
}
