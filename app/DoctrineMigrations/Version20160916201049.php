<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20160916201049 extends AbstractMigration implements ContainerAwareInterface
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
        $this->skipIf($schema->getTable($this->getTable('config'))->hasColumn('pocket_consumer_key'), 'It seems that you already played this migration.');

        $this->addSql('ALTER TABLE "'.$this->getTable('config').'" ADD pocket_consumer_key VARCHAR(255) DEFAULT NULL');
        $this->addSql('DELETE FROM "'.$this->getTable('craue_config_setting')."\" WHERE name = 'pocket_consumer_key';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->skipIf($this->connection->getDatabasePlatform()->getName() == 'sqlite', 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

        $this->addSql('ALTER TABLE "'.$this->getTable('config').'" DROP pocket_consumer_key');
        $this->addSql('INSERT INTO "'.$this->getTable('craue_config_setting')."\" (name, value, section) VALUES ('pocket_consumer_key', NULL, 'import')");
    }
}
