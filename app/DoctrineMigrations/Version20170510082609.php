<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Changed length for username, username_canonical, email and email_canonical fields in wallabag_user table.
 */
class Version20170510082609 extends WallabagMigration
{
    private $fields = [
        'username',
        'username_canonical',
        'email',
        'email_canonical',
    ];

    public function up(Schema $schema)
    {
        $this->skipIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'This migration only apply to MySQL');

        foreach ($this->fields as $field) {
            $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE ' . $field . ' ' . $field . ' VARCHAR(180) NOT NULL;');
        }
    }

    public function down(Schema $schema)
    {
        $this->skipIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'This migration only apply to MySQL');

        foreach ($this->fields as $field) {
            $this->addSql('ALTER TABLE ' . $this->getTable('user') . ' CHANGE ' . $field . ' ' . $field . ' VARCHAR(255) NOT NULL;');
        }
    }
}
