<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Methods and properties removed from `FOS\UserBundle\Model\User`.
 *
 * - `$expired`
 * - `$credentialsExpired`
 * - `setExpired()` (use `setExpiresAt(\DateTime::now()` instead)
 * - `setCredentialsExpired()` (use `setCredentialsExpireAt(\DateTime::now()` instead)
 *
 * You need to drop the fields `expired` and `credentials_expired` from your database
 * schema, because they aren't mapped anymore.
 */
class Version20161122203647 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $userTable = $schema->getTable($this->getTable('user'));

        $this->skipIf(false === $userTable->hasColumn('expired') || false === $userTable->hasColumn('credentials_expired'), 'It seems that you already played this migration.');

        $userTable->dropColumn('expired');
        $userTable->dropColumn('credentials_expired');
    }

    public function down(Schema $schema): void
    {
        $userTable = $schema->getTable($this->getTable('user'));

        $this->skipIf(true === $userTable->hasColumn('expired') || true === $userTable->hasColumn('credentials_expired'), 'It seems that you already played this migration.');

        $userTable->addColumn('expired', 'smallint', ['notnull' => false]);
        $userTable->addColumn('credentials_expired', 'smallint', ['notnull' => false]);
    }
}
