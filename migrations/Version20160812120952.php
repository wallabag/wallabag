<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Added name field on wallabag_oauth2_clients.
 */
class Version20160812120952 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $clientsTable = $schema->getTable($this->getTable('oauth2_clients'));
        $this->skipIf($clientsTable->hasColumn('name'), 'It seems that you already played this migration.');

        if ($this->connection->getDatabasePlatform() instanceof SqlitePlatform) {
            // Can't use $clientsTable->addColumn('name', 'blob');
            // because of the error:
            // SQLSTATE[HY000]: General error: 1 Cannot add a NOT NULL column with default value NULL
            $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('oauth2_clients', true) . ' AS SELECT id, random_id, redirect_uris, secret, allowed_grant_types FROM ' . $this->getTable('oauth2_clients', true));
            $this->addSql('DROP TABLE ' . $this->getTable('oauth2_clients', true));
            $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_clients', true) . ' (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, random_id VARCHAR(255) NOT NULL COLLATE BINARY, secret VARCHAR(255) NOT NULL COLLATE BINARY, redirect_uris CLOB NOT NULL, allowed_grant_types CLOB NOT NULL, name CLOB NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_635D765EA76ED395 FOREIGN KEY (user_id) REFERENCES "' . $this->getTable('user', true) . '" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $this->getTable('oauth2_clients', true) . ' (id, random_id, redirect_uris, secret, allowed_grant_types) SELECT id, random_id, redirect_uris, secret, allowed_grant_types FROM __temp__' . $this->getTable('oauth2_clients', true));
            $this->addSql('DROP TABLE __temp__' . $this->getTable('oauth2_clients', true));
            $this->addSql('CREATE INDEX IDX_635D765EA76ED395 ON ' . $this->getTable('oauth2_clients', true) . ' (user_id)');
        } else {
            $clientsTable->addColumn('name', 'blob');
        }
    }

    public function down(Schema $schema): void
    {
        $clientsTable = $schema->getTable($this->getTable('oauth2_clients'));

        if ($this->connection->getDatabasePlatform() instanceof SqlitePlatform) {
            $this->addSql('DROP INDEX IDX_635D765EA76ED395');
            $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('oauth2_clients', true) . ' AS SELECT id, random_id, redirect_uris, secret, allowed_grant_types FROM ' . $this->getTable('oauth2_clients', true));
            $this->addSql('DROP TABLE ' . $this->getTable('oauth2_clients', true));
            $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_clients', true) . ' (id INTEGER NOT NULL, random_id VARCHAR(255) NOT NULL, secret VARCHAR(255) NOT NULL, redirect_uris CLOB NOT NULL COLLATE BINARY, allowed_grant_types CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id))');
            $this->addSql('INSERT INTO ' . $this->getTable('oauth2_clients', true) . ' (id, random_id, redirect_uris, secret, allowed_grant_types) SELECT id, random_id, redirect_uris, secret, allowed_grant_types FROM __temp__' . $this->getTable('oauth2_clients', true));
            $this->addSql('DROP TABLE __temp__' . $this->getTable('oauth2_clients', true));
        } else {
            $clientsTable->dropColumn('name');
        }
    }
}
