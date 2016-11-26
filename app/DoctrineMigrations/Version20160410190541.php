<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version20160410190541 extends AbstractMigration implements ContainerAwareInterface
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
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('uuid'), 'It seems that you already played this migration.');

        $entryTable->addColumn('uuid', 'guid', [
            'notnull' => false,
        ]);
        $this->addSql('INSERT INTO '.$this->getTable('craue_config_setting')." (name, value, section) VALUES ('share_public', '1', 'entry')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $entryTable->dropColumn('uuid');

        $this->addSql('DELETE FROM '.$this->getTable('craue_config_setting')." WHERE name = 'share_public'");
    }
}
