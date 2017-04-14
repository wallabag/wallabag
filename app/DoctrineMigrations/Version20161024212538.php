<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Added user_id column on oauth2_clients to prevent users to delete API clients from other users.
 */
class Version20161024212538 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $constraintName = 'IDX_user_oauth_client';

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function getTable($tableName)
    {
        return $this->container->getParameter('database_table_prefix').$tableName;
    }

    /**
     * @param Schema $schema
     */
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

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $clientsTable = $schema->getTable($this->getTable('oauth2_clients'));

        $this->skipIf(!$clientsTable->hasColumn('user_id'), 'It seems that you already played this migration.');

        $clientsTable->dropColumn('user_id', 'integer');

        if ($this->connection->getDatabasePlatform()->getName() != 'sqlite') {
            $clientsTable->removeForeignKey($this->constraintName);
        }
    }
}
