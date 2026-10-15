<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

final class Version20261015190000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $feed = $schema->createTable($this->getTable('rss_feed', true));
        $feed->addColumn('id', 'integer', ['autoincrement' => true]);
        $feed->addColumn('user_id', 'integer');
        $feed->addColumn('url', 'text');
        $feed->addColumn('url_hash', 'string', ['length' => 64]);
        $feed->addColumn('name', 'string', ['length' => 255]);
        $feed->addColumn('tags', 'json');
        $feed->addColumn('max_items', 'integer');
        $feed->addColumn('enabled', 'boolean');
        $feed->addColumn('next_fetch_at', 'datetime', ['notnull' => false]);
        $feed->addColumn('last_fetch_at', 'datetime', ['notnull' => false]);
        $feed->addColumn('last_error', 'text', ['notnull' => false]);
        $feed->addColumn('imported_count', 'integer');
        $feed->setPrimaryKey(['id']);
        $feed->addUniqueIndex(['user_id', 'url_hash'], 'rss_owner_url');
        $feed->addForeignKeyConstraint($this->getTable('user', true), ['user_id'], ['id'], ['onDelete' => 'CASCADE']);
        $seen = $schema->createTable($this->getTable('rss_seen_item', true));
        $seen->addColumn('id', 'integer', ['autoincrement' => true]);
        $seen->addColumn('feed_id', 'integer');
        $seen->addColumn('url_hash', 'string', ['length' => 64]);
        $seen->addColumn('url', 'text');
        $seen->setPrimaryKey(['id']);
        $seen->addUniqueIndex(['feed_id', 'url_hash'], 'rss_feed_item');
        $seen->addForeignKeyConstraint($feed, ['feed_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable($this->getTable('rss_seen_item', true));
        $schema->dropTable($this->getTable('rss_feed', true));
    }
}
