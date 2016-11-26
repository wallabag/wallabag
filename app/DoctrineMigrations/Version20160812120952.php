<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20160812120952 extends AbstractMigration implements ContainerAwareInterface
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
        $this->skipIf($clientsTable->hasColumn('name'), 'It seems that you already played this migration.');

        $clientsTable->addColumn('name', 'blob');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $clientsTable = $schema->getTable($this->getTable('oauth2_clients'));
        $clientsTable->dropColumn('name');
    }
}
