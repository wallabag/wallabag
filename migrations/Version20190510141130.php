<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\SkipMigration;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Enable cascade delete when deleting a user on:
 *     - oauth2_access_tokens
 *     - oauth2_clients
 *     - oauth2_refresh_tokens
 *     - oauth2_auth_codes.
 */
final class Version20190510141130 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('DROP INDEX IDX_368A4209A76ED395');
                $this->addSql('DROP INDEX IDX_368A420919EB6921');
                $this->addSql('DROP INDEX UNIQ_368A42095F37A13B');
                $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('oauth2_access_tokens', true) . ' AS SELECT id, client_id, user_id, token, expires_at, scope FROM ' . $this->getTable('oauth2_access_tokens', true));
                $this->addSql('DROP TABLE ' . $this->getTable('oauth2_access_tokens', true));
                $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_access_tokens', true) . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, expires_at INTEGER DEFAULT NULL, token VARCHAR(191) NOT NULL, scope VARCHAR(191) NULL, CONSTRAINT FK_368A420919EB6921 FOREIGN KEY (client_id) REFERENCES ' . $this->getTable('oauth2_clients', true) . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_368A4209A76ED395 FOREIGN KEY (user_id) REFERENCES "' . $this->getTable('oauth2_clients', true) . '" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO ' . $this->getTable('oauth2_access_tokens', true) . ' (id, client_id, user_id, token, expires_at, scope) SELECT id, client_id, user_id, token, expires_at, scope FROM __temp__' . $this->getTable('oauth2_access_tokens', true));
                $this->addSql('DROP TABLE __temp__' . $this->getTable('oauth2_access_tokens', true));
                $this->addSql('CREATE INDEX IDX_368A4209A76ED395 ON ' . $this->getTable('oauth2_access_tokens', true) . ' (user_id)');
                $this->addSql('CREATE INDEX IDX_368A420919EB6921 ON ' . $this->getTable('oauth2_access_tokens', true) . ' (client_id)');

                $this->addSql('DROP INDEX IDX_635D765EA76ED395');
                $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('oauth2_clients', true) . ' AS SELECT id, user_id, random_id, secret, redirect_uris, allowed_grant_types, name FROM ' . $this->getTable('oauth2_clients', true));
                $this->addSql('DROP TABLE ' . $this->getTable('oauth2_clients', true));
                $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_clients', true) . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, random_id VARCHAR(255) NOT NULL COLLATE BINARY, secret VARCHAR(255) NOT NULL COLLATE BINARY, name CLOB NOT NULL COLLATE BINARY, redirect_uris CLOB NOT NULL, allowed_grant_types CLOB NOT NULL, CONSTRAINT FK_635D765EA76ED395 FOREIGN KEY (user_id) REFERENCES "' . $this->getTable('oauth2_clients', true) . '" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO ' . $this->getTable('oauth2_clients', true) . ' (id, user_id, random_id, secret, redirect_uris, allowed_grant_types, name) SELECT id, user_id, random_id, secret, redirect_uris, allowed_grant_types, name FROM __temp__' . $this->getTable('oauth2_clients', true));
                $this->addSql('DROP TABLE __temp__' . $this->getTable('oauth2_clients', true));
                $this->addSql('CREATE INDEX IDX_635D765EA76ED395 ON ' . $this->getTable('oauth2_clients', true) . ' (user_id)');

                $this->addSql('DROP INDEX IDX_20C9FB24A76ED395');
                $this->addSql('DROP INDEX IDX_20C9FB2419EB6921');
                $this->addSql('DROP INDEX UNIQ_20C9FB245F37A13B');
                $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('oauth2_refresh_tokens', true) . ' AS SELECT id, client_id, user_id, token, expires_at, scope FROM ' . $this->getTable('oauth2_refresh_tokens', true));
                $this->addSql('DROP TABLE ' . $this->getTable('oauth2_refresh_tokens', true));
                $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_refresh_tokens', true) . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, expires_at INTEGER DEFAULT NULL, token VARCHAR(191) NOT NULL, scope VARCHAR(191) NULL, CONSTRAINT FK_20C9FB2419EB6921 FOREIGN KEY (client_id) REFERENCES ' . $this->getTable('oauth2_clients', true) . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_20C9FB24A76ED395 FOREIGN KEY (user_id) REFERENCES "' . $this->getTable('oauth2_clients', true) . '" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO ' . $this->getTable('oauth2_refresh_tokens', true) . ' (id, client_id, user_id, token, expires_at, scope) SELECT id, client_id, user_id, token, expires_at, scope FROM __temp__' . $this->getTable('oauth2_refresh_tokens', true));
                $this->addSql('DROP TABLE __temp__' . $this->getTable('oauth2_refresh_tokens', true));
                $this->addSql('CREATE INDEX IDX_20C9FB24A76ED395 ON ' . $this->getTable('oauth2_refresh_tokens', true) . ' (user_id)');
                $this->addSql('CREATE INDEX IDX_20C9FB2419EB6921 ON ' . $this->getTable('oauth2_refresh_tokens', true) . ' (client_id)');

                $this->addSql('DROP INDEX IDX_EE52E3FAA76ED395');
                $this->addSql('DROP INDEX IDX_EE52E3FA19EB6921');
                $this->addSql('DROP INDEX UNIQ_EE52E3FA5F37A13B');
                $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('oauth2_auth_codes', true) . ' AS SELECT id, client_id, user_id, token, redirect_uri, expires_at, scope FROM ' . $this->getTable('oauth2_auth_codes', true));
                $this->addSql('DROP TABLE ' . $this->getTable('oauth2_auth_codes', true));
                $this->addSql('CREATE TABLE ' . $this->getTable('oauth2_auth_codes', true) . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, client_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, redirect_uri CLOB NOT NULL COLLATE BINARY, expires_at INTEGER DEFAULT NULL, token VARCHAR(191) NOT NULL, scope VARCHAR(191) NULL, CONSTRAINT FK_EE52E3FA19EB6921 FOREIGN KEY (client_id) REFERENCES ' . $this->getTable('oauth2_clients', true) . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EE52E3FAA76ED395 FOREIGN KEY (user_id) REFERENCES "' . $this->getTable('oauth2_clients', true) . '" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
                $this->addSql('INSERT INTO ' . $this->getTable('oauth2_auth_codes', true) . ' (id, client_id, user_id, token, redirect_uri, expires_at, scope) SELECT id, client_id, user_id, token, redirect_uri, expires_at, scope FROM __temp__' . $this->getTable('oauth2_auth_codes', true));
                $this->addSql('DROP TABLE __temp__' . $this->getTable('oauth2_auth_codes', true));
                $this->addSql('CREATE INDEX IDX_EE52E3FAA76ED395 ON ' . $this->getTable('oauth2_auth_codes', true) . ' (user_id)');
                $this->addSql('CREATE INDEX IDX_EE52E3FA19EB6921 ON ' . $this->getTable('oauth2_auth_codes', true) . ' (client_id)');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_access_tokens') . ' DROP FOREIGN KEY FK_368A4209A76ED395');
                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_access_tokens') . ' ADD CONSTRAINT FK_368A4209A76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) ON DELETE CASCADE');

                if ($schema->getTable($this->getTable('oauth2_clients'))->hasForeignKey('IDX_user_oauth_client')) {
                    $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_clients') . ' DROP FOREIGN KEY IDX_user_oauth_client');
                    $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_clients') . ' ADD CONSTRAINT FK_635D765EA76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id)');
                }

                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_refresh_tokens') . ' DROP FOREIGN KEY FK_20C9FB24A76ED395');
                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_refresh_tokens') . ' ADD CONSTRAINT FK_20C9FB24A76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) ON DELETE CASCADE');

                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_auth_codes') . ' DROP FOREIGN KEY FK_EE52E3FAA76ED395');
                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_auth_codes') . ' ADD CONSTRAINT FK_EE52E3FAA76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) ON DELETE CASCADE');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_access_tokens') . ' DROP CONSTRAINT FK_368A4209A76ED395');
                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_access_tokens') . ' ADD CONSTRAINT FK_368A4209A76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_clients') . ' DROP CONSTRAINT idx_user_oauth_client');
                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_clients') . ' ADD CONSTRAINT FK_635D765EA76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_refresh_tokens') . ' DROP CONSTRAINT FK_20C9FB24A76ED395');
                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_refresh_tokens') . ' ADD CONSTRAINT FK_20C9FB24A76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_auth_codes') . ' DROP CONSTRAINT FK_EE52E3FAA76ED395');
                $this->addSql('ALTER TABLE ' . $this->getTable('oauth2_auth_codes') . ' ADD CONSTRAINT FK_EE52E3FAA76ED395 FOREIGN KEY (user_id) REFERENCES ' . $this->getTable('user') . ' (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
                break;
        }
    }

    public function down(Schema $schema): void
    {
        throw new SkipMigration('Too complex ...');
    }
}
