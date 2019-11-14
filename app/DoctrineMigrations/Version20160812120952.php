<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Added name field on wallabag_oauth2_clients.
 */
class Version20160812120952 extends WallabagMigration
{
    public function up(Schema $schema)
    {
        $clientsTable = $schema->getTable($this->getTable('oauth2_clients'));
        $this->skipIf($clientsTable->hasColumn('name'), 'It seems that you already played this migration.');

        if ('sqlite' === $this->connection->getDatabasePlatform()->getName()) {
            // Can't use $clientsTable->addColumn('name', 'blob');
            // because of the error:
            // SQLSTATE[HY000]: General error: 1 Cannot add a NOT NULL column with default value NULL
            $databaseTablePrefix = $this->container->getParameter('database_table_prefix');
            $this->addSql('CREATE TEMPORARY TABLE __temp__' . $databaseTablePrefix . 'oauth2_clients AS SELECT id, random_id, redirect_uris, secret, allowed_grant_types FROM ' . $databaseTablePrefix . 'oauth2_clients');
            $this->addSql('DROP TABLE ' . $databaseTablePrefix . 'oauth2_clients');
            $this->addSql('CREATE TABLE ' . $databaseTablePrefix . 'oauth2_clients (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, random_id VARCHAR(255) NOT NULL COLLATE BINARY, secret VARCHAR(255) NOT NULL COLLATE BINARY, redirect_uris CLOB NOT NULL, allowed_grant_types CLOB NOT NULL, name CLOB NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_635D765EA76ED395 FOREIGN KEY (user_id) REFERENCES "' . $databaseTablePrefix . 'user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO ' . $databaseTablePrefix . 'oauth2_clients (id, random_id, redirect_uris, secret, allowed_grant_types) SELECT id, random_id, redirect_uris, secret, allowed_grant_types FROM __temp__' . $databaseTablePrefix . 'oauth2_clients');
            $this->addSql('DROP TABLE __temp__' . $databaseTablePrefix . 'oauth2_clients');
            $this->addSql('CREATE INDEX IDX_635D765EA76ED395 ON ' . $databaseTablePrefix . 'oauth2_clients (user_id)');
        } else {
            $clientsTable->addColumn('name', 'blob');
        }
    }

    public function down(Schema $schema)
    {
        $clientsTable = $schema->getTable($this->getTable('oauth2_clients'));

        if ('sqlite' === $this->connection->getDatabasePlatform()->getName()) {
            $databaseTablePrefix = $this->container->getParameter('database_table_prefix');
            $this->addSql('DROP INDEX IDX_635D765EA76ED395');
            $this->addSql('CREATE TEMPORARY TABLE __temp__' . $databaseTablePrefix . 'oauth2_clients AS SELECT id, random_id, redirect_uris, secret, allowed_grant_types FROM ' . $databaseTablePrefix . 'oauth2_clients');
            $this->addSql('DROP TABLE ' . $databaseTablePrefix . 'oauth2_clients');
            $this->addSql('CREATE TABLE ' . $databaseTablePrefix . 'oauth2_clients (id INTEGER NOT NULL, random_id VARCHAR(255) NOT NULL, secret VARCHAR(255) NOT NULL, redirect_uris CLOB NOT NULL COLLATE BINARY, allowed_grant_types CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id))');
            $this->addSql('INSERT INTO ' . $databaseTablePrefix . 'oauth2_clients (id, random_id, redirect_uris, secret, allowed_grant_types) SELECT id, random_id, redirect_uris, secret, allowed_grant_types FROM __temp__' . $databaseTablePrefix . 'oauth2_clients');
            $this->addSql('DROP TABLE __temp__' . $databaseTablePrefix . 'oauth2_clients');
        } else {
            $clientsTable->dropColumn('name');
        }
    }
}
