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
        return $this->container->getParameter('database_table_prefix') . $tableName;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if ($this->connection->getDatabasePlatform()->getName() == 'sqlite') {
            $this->addSql('ALTER TABLE '.$this->getTable('oauth2_clients').' ADD name longtext DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE '.$this->getTable('oauth2_clients').' ADD name longtext COLLATE \'utf8_unicode_ci\' DEFAULT NULL');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() == 'sqlite', 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

        $this->addSql('ALTER TABLE '.$this->getTable('oauth2_clients').' DROP COLUMN name');
    }
}
