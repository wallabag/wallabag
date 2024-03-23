<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added user_id column on oauth2_clients to prevent users to delete API clients from other users.
 */
class Version20161024212538 extends WallabagMigration
{
    private $constraintName = 'IDX_user_oauth_client';

    public function up(Schema $schema): void
    {
        $clientsTable = $schema->getTable($this->getTable('oauth2_clients'));

        if ($clientsTable->hasColumn('user_id')) {
            $this->write('It seems that you already played this migration.');

            return;
        }

        $clientsTable->addColumn('user_id', 'integer', ['notnull' => false]);

        $clientsTable->addForeignKeyConstraint(
            $this->getTable('user'),
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            $this->constraintName
        );
    }

    public function down(Schema $schema): void
    {
        $clientsTable = $schema->getTable($this->getTable('oauth2_clients'));

        if ($clientsTable->hasColumn('user_id')) {
            $this->write('It seems that you already played this migration.');

            return;
        }

        $clientsTable->dropColumn('user_id', 'integer');

        if (!$this->connection->getDatabasePlatform() instanceof SqlitePlatform) {
            $clientsTable->removeForeignKey($this->constraintName);
        }
    }
}
