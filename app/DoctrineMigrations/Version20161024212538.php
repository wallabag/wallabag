<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20161024212538 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

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

        $clientsTable->addColumn('user_id', 'integer');

        $clientsTable->addForeignKeyConstraint(
            $this->getTable('user'),
            array('user_id'),
            array('id'),
            array('onDelete' => 'CASCADE')
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
