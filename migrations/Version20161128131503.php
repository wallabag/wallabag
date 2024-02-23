<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Removed locked, credentials_expire_at and expires_at.
 */
class Version20161128131503 extends WallabagMigration
{
    private $fields = [
        'locked' => 'smallint',
        'credentials_expire_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function up(Schema $schema): void
    {
        $userTable = $schema->getTable($this->getTable('user'));

        foreach ($this->fields as $field => $type) {
            $this->skipIf(!$userTable->hasColumn($field), 'It seems that you already played this migration.');
            $userTable->dropColumn($field);
        }
    }

    public function down(Schema $schema): void
    {
        $userTable = $schema->getTable($this->getTable('user'));

        foreach ($this->fields as $field => $type) {
            $this->skipIf($userTable->hasColumn($field), 'It seems that you already played this migration.');
            $userTable->addColumn($field, $type, ['notnull' => false]);
        }
    }
}
