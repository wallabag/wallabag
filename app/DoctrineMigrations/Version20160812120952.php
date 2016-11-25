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
        $this->skipIf($schema->getTable($this->getTable('oauth2_clients'))->hasColumn('name'), 'It seems that you already played this migration.');

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'sqlite':
                $this->addSql('ALTER TABLE '.$this->getTable('oauth2_clients').' ADD name longtext DEFAULT NULL');
                break;

            case 'mysql':
                $this->addSql('ALTER TABLE '.$this->getTable('oauth2_clients').' ADD name longtext COLLATE \'utf8_unicode_ci\' DEFAULT NULL');
                break;

            case 'postgresql':
                $this->addSql('ALTER TABLE '.$this->getTable('oauth2_clients').' ADD name text DEFAULT NULL');
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
