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
        $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' ADD googleAuthenticatorSecret VARCHAR(191) DEFAULT NULL, CHANGE twoFactorAuthentication emailTwoFactor BOOLEAN NOT NULL, DROP trusted');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `' . $this->getTable('user') . '` DROP googleAuthenticatorSecret, CHANGE emailtwofactor twoFactorAuthentication BOOLEAN NOT NULL, ADD trusted TEXT DEFAULT NULL');
    }
}
