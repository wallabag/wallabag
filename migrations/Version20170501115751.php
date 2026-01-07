<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add site credential table to store username & password for some website (behind authentication or paywall).
 */
class Version20170501115751 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf($schema->hasTable($this->getTable('site_credential')), 'It seems that you already played this migration.');

        $table = $schema->createTable($this->getTable('site_credential'));
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer');
        $table->addColumn('host', 'string', ['length' => 255]);
        $table->addColumn('username', 'text');
        $table->addColumn('password', 'text');
        $table->addColumn('createdAt', 'datetime');
        $table->addIndex(['user_id'], 'idx_user');
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint($this->getTable('user'), ['user_id'], ['id'], [], 'fk_user');

        if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $schema->dropSequence('site_credential_id_seq');
            $schema->createSequence('site_credential_id_seq');
        }
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable($this->getTable('site_credential'));
    }
}
