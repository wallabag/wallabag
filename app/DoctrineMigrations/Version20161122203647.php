<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Methods and properties removed from `FOS\UserBundle\Model\User`.
 *
 * - `$expired`
 * - `$credentialsExpired`
 * - `setExpired()` (use `setExpiresAt(\DateTime::now()` instead)
 * - `setCredentialsExpired()` (use `setCredentialsExpireAt(\DateTime::now()` instead)
 *
 * You need to drop the fields `expired` and `credentials_expired` from your database
 * schema, because they aren't mapped anymore.
 */
class Version20161122203647 extends AbstractMigration implements ContainerAwareInterface
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
        $this->skipIf($this->connection->getDatabasePlatform()->getName() === 'sqlite', 'This up migration can\'t be executed on SQLite databases, because SQLite don\'t support DROP COLUMN.');

        $this->skipIf(false === $schema->getTable($this->getTable('user'))->hasColumn('expired'), 'It seems that you already played this migration.');

        $this->addSql('ALTER TABLE '.$this->getTable('user').' DROP expired');

        $this->skipIf(false === $schema->getTable($this->getTable('user'))->hasColumn('credentials_expired'), 'It seems that you already played this migration.');

        $this->addSql('ALTER TABLE '.$this->getTable('user').' DROP credentials_expired');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE '.$this->getTable('user').' ADD expired tinyint(1) NULL DEFAULT 0');
        $this->addSql('ALTER TABLE '.$this->getTable('user').' ADD credentials_expired tinyint(1) NULL DEFAULT 0');
    }
}
