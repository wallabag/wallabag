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
        $configTable = $schema->getTable($this->getTable('config'));

        $this->skipIf($configTable->hasColumn('pocket_consumer_key'), 'It seems that you already played this migration.');

        $userTable->addColumn('pocket_consumer_key', 'string');
        $this->addSql('DELETE FROM '.$this->getTable('craue_config_setting')." WHERE name = 'pocket_consumer_key';");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $configTable = $schema->getTable($this->getTable('config'));
        $clientsTable->dropColumn('pocket_consumer_key');
        $this->addSql('INSERT INTO '.$this->getTable('craue_config_setting')." (name, value, section) VALUES ('pocket_consumer_key', NULL, 'import')");
    }
}
