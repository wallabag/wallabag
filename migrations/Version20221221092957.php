<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Remove the deprecated (and removed in DBAL v3) `json_array` type.
 */
final class Version20221221092957 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $userTable = $this->getTable('user');
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_user AS SELECT id, username, username_canonical, email, email_canonical, enabled, password, last_login, password_requested_at, name, created_at, updated_at, authCode, emailTwoFactor, salt, confirmation_token, roles, googleAuthenticatorSecret, backupCodes FROM ' . $userTable);
                $this->addSql('DROP TABLE ' . $userTable);
                $this->addSql('CREATE TABLE ' . $userTable . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL --(DC2Type:array)
                , name CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, googleAuthenticatorSecret VARCHAR(255) DEFAULT NULL, backupCodes CLOB DEFAULT NULL --(DC2Type:json)
                , emailTwoFactor BOOLEAN NOT NULL)');
                $this->addSql('INSERT INTO ' . $userTable . ' (id, username, username_canonical, email, email_canonical, enabled, password, last_login, password_requested_at, name, created_at, updated_at, authCode, emailTwoFactor, salt, confirmation_token, roles, googleAuthenticatorSecret, backupCodes) SELECT id, username, username_canonical, email, email_canonical, enabled, password, last_login, password_requested_at, name, created_at, updated_at, authCode, emailTwoFactor, salt, confirmation_token, roles, googleAuthenticatorSecret, backupCodes FROM __temp__wallabag_user');
                $this->addSql('DROP TABLE __temp__wallabag_user');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON ' . $userTable . ' (username_canonical)');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON ' . $userTable . ' (email_canonical)');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON ' . $userTable . ' (confirmation_token)');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $userTable . ' CHANGE backupCodes backupCodes JSON DEFAULT NULL');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $userTable . ' ALTER backupcodes TYPE JSON USING backupcodes::json');
                break;
        }
    }

    public function down(Schema $schema): void
    {
        $userTable = $this->getTable('user');
        $platform = $this->connection->getDatabasePlatform();

        switch (true) {
            case $platform instanceof SqlitePlatform:
                $this->addSql('CREATE TEMPORARY TABLE __temp__wallabag_user AS SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, googleAuthenticatorSecret, backupCodes, emailTwoFactor FROM ' . $userTable);
                $this->addSql('DROP TABLE ' . $userTable);
                $this->addSql('CREATE TABLE ' . $userTable . ' (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled BOOLEAN NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles CLOB NOT NULL --(DC2Type:array)
                , name CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, authCode INTEGER DEFAULT NULL, googleAuthenticatorSecret VARCHAR(255) DEFAULT NULL, backupCodes CLOB DEFAULT NULL --(DC2Type:json_array)
                , emailTwoFactor BOOLEAN NOT NULL)');
                $this->addSql('INSERT INTO ' . $userTable . ' (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, googleAuthenticatorSecret, backupCodes, emailTwoFactor) SELECT id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, name, created_at, updated_at, authCode, googleAuthenticatorSecret, backupCodes, emailTwoFactor FROM __temp__wallabag_user');
                $this->addSql('DROP TABLE __temp__wallabag_user');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E592FC23A8 ON ' . $userTable . ' (username_canonical)');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E5A0D96FBF ON ' . $userTable . ' (email_canonical)');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_1D63E7E5C05FB297 ON ' . $userTable . ' (confirmation_token)');
                break;
            case $platform instanceof MySQLPlatform:
                $this->addSql('ALTER TABLE ' . $userTable . ' CHANGE backupCodes backupCodes JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
                break;
            case $platform instanceof PostgreSQLPlatform:
                $this->addSql('ALTER TABLE ' . $userTable . ' ALTER backupCodes TYPE TEXT');
                break;
        }
    }
}
