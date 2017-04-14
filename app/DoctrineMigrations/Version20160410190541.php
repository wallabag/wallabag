<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Added foreign keys for account resetting.
 */
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

        $this->skipIf($entryTable->hasColumn('uid') || $entryTable->hasColumn('uuid'), 'It seems that you already played this migration.');

        $entryTable->addColumn('uid', 'string', [
            'notnull' => false,
            'length' => 23,
        ]);

        $sharePublic = $this->container
            ->get('doctrine.orm.default_entity_manager')
            ->getConnection()
            ->fetchArray('SELECT * FROM '.$this->getTable('craue_config_setting')." WHERE name = 'share_public'");

        if (false === $sharePublic) {
            $this->addSql('INSERT INTO '.$this->getTable('craue_config_setting')." (name, value, section) VALUES ('share_public', '1', 'entry')");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $entryTable->dropColumn('uid');

        $this->addSql('DELETE FROM '.$this->getTable('craue_config_setting')." WHERE name = 'share_public'");
    }
}
