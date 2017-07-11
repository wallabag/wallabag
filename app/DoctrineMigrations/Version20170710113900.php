<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Added dn field on wallabag_users
 */
class Version20170710113900 extends AbstractMigration implements ContainerAwareInterface
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
        $usersTable = $schema->getTable($this->getTable('user'));

        $this->skipIf($usersTable->hasColumn('dn'), 'It seems that you already played this migration.');

        $usersTable->addColumn('dn', 'text', [
            'default' => null,
            'notnull' => false,
        ]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $usersTable = $schema->getTable($this->getTable('user'));
        $usersTable->dropColumn('dn');
    }
}

