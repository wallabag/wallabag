<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add boolean for two-step setup for google authenticator.
 */
final class Version20250413133131 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $userTable = $schema->getTable($this->getTable('user'));

        $this->skipIf($userTable->hasColumn('google_authenticator'), 'It seems that you already played this migration.');

        $userTable->addColumn('google_authenticator', 'boolean', [
            'default' => false,
            'notnull' => true,
        ]);
    }

    /**
     * Query to update data in user table, as it's not possible to perform this in the `up` method.
     */
    public function postUp(Schema $schema): void
    {
        $this->skipIf(!$schema->getTable($this->getTable('user'))->hasColumn('google_authenticator'), 'Unable to update google_authenticator column');
        $this->connection->executeQuery(
            'UPDATE ' . $this->getTable('user') . ' SET google_authenticator = :googleAuthenticator WHERE googleAuthenticatorSecret IS NOT NULL AND googleAuthenticatorSecret <> :emptyString',
            [
                'googleAuthenticator' => true,
                'emptyString' => '',
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $userTable = $schema->getTable($this->getTable('user'));
        $userTable->dropColumn('google_authenticator');
    }
}
