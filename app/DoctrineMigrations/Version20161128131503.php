<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Removed locked, credentials_expire_at and expires_at.
 */
class Version20161128131503 extends AbstractMigration implements ContainerAwareInterface
{
    private $fields = [
        'locked' => 'smallint',
        'credentials_expire_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $userTable = $schema->getTable($this->getTable('user'));

        foreach ($this->fields as $field => $type) {
            $this->skipIf(!$userTable->hasColumn($field), 'It seems that you already played this migration.');
            $userTable->dropColumn($field);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $userTable = $schema->getTable($this->getTable('user'));

        foreach ($this->fields as $field => $type) {
            $this->skipIf($userTable->hasColumn($field), 'It seems that you already played this migration.');
            $userTable->addColumn($field, $type, ['notnull' => false]);
        }
    }

    private function getTable($tableName)
    {
        return $this->container->getParameter('database_table_prefix') . $tableName;
    }
}
