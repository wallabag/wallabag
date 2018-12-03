<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Add 2fa OTP (named google authenticator).
 */
final class Version20181202073750 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $tableName = $this->getTable('annotation');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                break;
            case 'mysql':
                $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ADD googleAuthenticatorSecret VARCHAR(191) DEFAULT NULL, CHANGE twoFactorAuthentication emailTwoFactor BOOLEAN NOT NULL, DROP trusted, ADD backupCodes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
                break;
            case 'postgresql':
                break;
        }
    }

    public function down(Schema $schema): void
    {
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                break;
            case 'mysql':
                $this->addSql('ALTER TABLE `' . $this->getTable('user') . '` DROP googleAuthenticatorSecret, CHANGE emailtwofactor twoFactorAuthentication BOOLEAN NOT NULL, ADD trusted TEXT DEFAULT NULL, DROP backupCodes');
                break;
            case 'postgresql':
                break;
        }
    }
}
