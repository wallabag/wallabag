<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add 2fa OTP stuff.
 */
final class Version20181202073750 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('DROP INDEX UNIQ_1D63E7E5C05FB297');
                $this->addSql('DROP INDEX UNIQ_1D63E7E5A0D96FBF');
                $this->addSql('DROP INDEX UNIQ_1D63E7E592FC23A8');
                $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('user', true) . ' AS SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, twoFactorAuthentication FROM ' . $this->getTable('user', true) . '');
                $this->addSql('DROP TABLE ' . $this->getTable('user', true) . '');
                $this->addSql('CREATE TABLE ' . $this->getTable('user', true) . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL COLLATE BINARY, username_canonical VARCHAR(180) NOT NULL COLLATE BINARY, email VARCHAR(180) NOT NULL COLLATE BINARY, email_canonical VARCHAR(180) NOT NULL COLLATE BINARY, enabled BOOLEAN NOT NULL, password VARCHAR(255) NOT NULL COLLATE BINARY, last_login DATETIME DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, name CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, emailTwoFactor BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, roles CLOB NOT NULL --(DC2Type:array)
                , googleAuthenticatorSecret VARCHAR(255) DEFAULT NULL, backupCodes CLOB DEFAULT NULL --(DC2Type:json_array)
                )');
                $this->addSql('INSERT INTO ' . $this->getTable('user', true) . ' (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, emailTwoFactor) SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, twoFactorAuthentication FROM __temp__' . $this->getTable('user', true) . '');
                $this->addSql('DROP TABLE __temp__' . $this->getTable('user', true) . '');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON ' . $this->getTable('user', true) . ' (confirmation_token)');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON ' . $this->getTable('user', true) . ' (email_canonical)');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON ' . $this->getTable('user', true) . ' (username_canonical)');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ADD googleAuthenticatorSecret VARCHAR(191) DEFAULT NULL');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE twoFactorAuthentication emailTwoFactor BOOLEAN NOT NULL');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' DROP trusted');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ADD backupCodes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ADD googleAuthenticatorSecret VARCHAR(191) DEFAULT NULL');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' RENAME COLUMN twofactorauthentication TO emailTwoFactor');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' DROP trusted');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ADD backupCodes TEXT DEFAULT NULL');
                break;
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('DROP INDEX UNIQ_1D63E7E592FC23A8');
                $this->addSql('DROP INDEX UNIQ_1D63E7E5A0D96FBF');
                $this->addSql('DROP INDEX UNIQ_1D63E7E5C05FB297');
                $this->addSql('CREATE TEMPORARY TABLE __temp__' . $this->getTable('user', true) . ' AS SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, emailTwoFactor FROM "' . $this->getTable('user', true) . '"');
                $this->addSql('DROP TABLE "' . $this->getTable('user', true) . '"');
                $this->addSql('CREATE TABLE "' . $this->getTable('user', true) . '" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, name CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, twoFactorAuthentication BOOLEAN NOT NULL, salt VARCHAR(255) NOT NULL COLLATE BINARY, confirmation_token VARCHAR(255) DEFAULT NULL COLLATE BINARY, roles CLOB NOT NULL COLLATE BINARY, trusted CLOB DEFAULT NULL COLLATE BINARY)');
                $this->addSql('INSERT INTO "' . $this->getTable('user', true) . '" (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, twoFactorAuthentication) SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, emailTwoFactor FROM __temp__' . $this->getTable('user', true) . '');
                $this->addSql('DROP TABLE __temp__' . $this->getTable('user', true) . '');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON "' . $this->getTable('user', true) . '" (username_canonical)');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON "' . $this->getTable('user', true) . '" (email_canonical)');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON "' . $this->getTable('user', true) . '" (confirmation_token)');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE `' . $this->getTable('user') . '` DROP googleAuthenticatorSecret');
                $this->addSql('ALTER TABLE `' . $this->getTable('user') . '` CHANGE emailtwofactor twoFactorAuthentication BOOLEAN NOT NULL');
                $this->addSql('ALTER TABLE `' . $this->getTable('user') . '` ADD trusted TEXT DEFAULT NULL');
                $this->addSql('ALTER TABLE `' . $this->getTable('user') . '` DROP backupCodes');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' DROP googleAuthenticatorSecret');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' RENAME COLUMN emailTwoFactor TO twofactorauthentication');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ADD trusted TEXT DEFAULT NULL');
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' DROP backupCodes');
                break;
        }
    }
}
