<?php

namespace Wallabag\CoreBundle\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class WallabagMigration extends AbstractMigration implements ContainerAwareInterface
{
    const UN_ESCAPED_TABLE = true;

    /**
     * @var ContainerInterface
     */
    protected $container;

    // because there are declared as abstract in `AbstractMigration` we need to delarer here too
    public function up(Schema $schema)
    {
    }

    public function down(Schema $schema)
    {
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function getTable($tableName, $unEscaped = false)
    {
        $table = $this->container->getParameter('database_table_prefix') . $tableName;

        if (self::UN_ESCAPED_TABLE === $unEscaped) {
            return $table;
        }

        // escape table name is handled using " on postgresql
        if ('postgresql' === $this->connection->getDatabasePlatform()->getName()) {
            return '"' . $table . '"';
        }

        // return escaped table
        return '`' . $table . '`';
    }
}
