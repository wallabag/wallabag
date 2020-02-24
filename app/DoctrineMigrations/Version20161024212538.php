<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\CoreBundle\Doctrine\WallabagMigration;

/**
 * Added user_id column on oauth2_clients to prevent users to delete API clients from other users.
 */
class Version20161024212538 extends WallabagMigration
{
    private $constraintName = 'IDX_user_oauth_client';

    public function up(Schema $schema)
    {
        $clientsTable = $schema->getTable($this->getTable('oauth2_clients'));

        $this->skipIf($clientsTable->hasColumn('user_id'), 'It seems that you already played this migration.');

        $clientsTable->addColumn('user_id', 'integer', ['notnull' => false]);

        $clientsTable->addForeignKeyConstraint(
            $this->getTable('user'),
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            $this->constraintName
        );
    }

    public function down(Schema $schema)
    {
        $clientsTable = $schema->getTable($this->getTable('oauth2_clients'));

        $this->skipIf(!$clientsTable->hasColumn('user_id'), 'It seems that you already played this migration.');

        $clientsTable->dropColumn('user_id', 'integer');

        if ('sqlite' !== $this->connection->getDatabasePlatform()->getName()) {
            $clientsTable->removeForeignKey($this->constraintName);
        }
    }
}
