<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20261119210315 extends WallabagMigration
{
    public function getDescription(): string
    {
        return 'Store collected feed articles and the last successful refresh';
    }

    public function up(Schema $schema): void
    {
        $items = $this->getTable('feed_item');
        $feeds = $this->getTable('feed_url');
        $index = $this->getIndexName('feed_item', 'feed_id');
        $uniqueIndex = $this->generateIdentifierName([$this->getTable('feed_item', true), 'feed_id', 'url_hash'], 'uniq');
        $foreignKey = $this->getForeignKeyName('feed_item', 'feed_id');

        switch (true) {
            case $this->platform instanceof MySQLPlatform:
                $this->addSql("CREATE TABLE $items (id INT AUTO_INCREMENT NOT NULL, feed_id INT NOT NULL, url LONGTEXT NOT NULL, url_hash VARCHAR(64) NOT NULL, title LONGTEXT DEFAULT NULL, image LONGTEXT DEFAULT NULL, published_at DATETIME DEFAULT NULL, is_trashed TINYINT(1) DEFAULT 0 NOT NULL, INDEX $index (feed_id), UNIQUE INDEX $uniqueIndex (feed_id, url_hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
                $this->addSql("ALTER TABLE $items ADD CONSTRAINT $foreignKey FOREIGN KEY (feed_id) REFERENCES $feeds (id) ON DELETE CASCADE");
                $this->addSql("ALTER TABLE $feeds ADD updated_at DATETIME DEFAULT NULL");
                break;
            case $this->platform instanceof PostgreSQLPlatform:
                $this->addSql('CREATE SEQUENCE feed_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
                $this->addSql("CREATE TABLE $items (id INT NOT NULL, feed_id INT NOT NULL, url TEXT NOT NULL, url_hash VARCHAR(64) NOT NULL, title TEXT DEFAULT NULL, image TEXT DEFAULT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_trashed BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))");
                $this->addSql("ALTER TABLE $items ADD CONSTRAINT $foreignKey FOREIGN KEY (feed_id) REFERENCES $feeds (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
                $this->addSql("ALTER TABLE $feeds ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL");
                $this->addSql("CREATE INDEX $index ON $items (feed_id)");
                $this->addSql("CREATE UNIQUE INDEX $uniqueIndex ON $items (feed_id, url_hash)");
                break;
            case $this->platform instanceof SqlitePlatform:
                $this->addSql("CREATE TABLE $items (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, feed_id INTEGER NOT NULL, url CLOB NOT NULL, url_hash VARCHAR(64) NOT NULL, title CLOB DEFAULT NULL, image CLOB DEFAULT NULL, published_at DATETIME DEFAULT NULL, is_trashed BOOLEAN DEFAULT 0 NOT NULL, CONSTRAINT $foreignKey FOREIGN KEY (feed_id) REFERENCES $feeds (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)");
                $this->addSql("ALTER TABLE $feeds ADD COLUMN updated_at DATETIME DEFAULT NULL");
                $this->addSql("CREATE INDEX $index ON $items (feed_id)");
                $this->addSql("CREATE UNIQUE INDEX $uniqueIndex ON $items (feed_id, url_hash)");
                break;
            default:
                $this->abortIf(true, 'Unsupported database platform.');
        }
    }

    public function down(Schema $schema): void
    {
        $feeds = $this->getTable('feed_url');
        $this->addSql('DROP TABLE ' . $this->getTable('feed_item'));

        switch (true) {
            case $this->platform instanceof PostgreSQLPlatform:
                $this->addSql('DROP SEQUENCE feed_item_id_seq');
                $this->addSql("ALTER TABLE $feeds DROP COLUMN updated_at");
                break;
            case $this->platform instanceof MySQLPlatform:
                $this->addSql("ALTER TABLE $feeds DROP COLUMN updated_at");
                break;
            case $this->platform instanceof SqlitePlatform:
                $this->dropSqliteUpdatedAt($feeds);
                break;
            default:
                $this->abortIf(true, 'Unsupported database platform.');
        }
    }

    private function dropSqliteUpdatedAt(string $feeds): void
    {
        if (version_compare((string) $this->connection->fetchOne('SELECT sqlite_version()'), '3.35.5', '>=')) {
            $this->addSql("ALTER TABLE $feeds DROP COLUMN updated_at");

            return;
        }

        $backup = $this->connection->quoteIdentifier('__temp__' . $this->getTable('feed_url', true));
        $user = $this->getTable('user');
        $index = $this->getIndexName('feed_url', 'user_id');
        $foreignKey = $this->getForeignKeyName('feed_url', 'user_id');
        $this->addSql("CREATE TEMPORARY TABLE $backup AS SELECT id, user_id, title, url FROM $feeds");
        $this->addSql("DROP TABLE $feeds");
        $this->addSql("CREATE TABLE $feeds (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, title CLOB NOT NULL, url CLOB NOT NULL, CONSTRAINT $foreignKey FOREIGN KEY (user_id) REFERENCES $user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)");
        $this->addSql("INSERT INTO $feeds (id, user_id, title, url) SELECT id, user_id, title, url FROM $backup");
        $this->addSql("DROP TABLE $backup");
        $this->addSql("CREATE INDEX $index ON $feeds (user_id)");
    }
}
