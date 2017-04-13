<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Migrations\SkipMigrationException;

/**
 * Added pocket_consumer_key field on wallabag_config.
 */
class Version20161001072726 extends AbstractMigration implements ContainerAwareInterface
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
        $this->skipIf($this->connection->getDatabasePlatform()->getName() == 'sqlite', 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

        // remove all FK from entry_tag
        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'mysql':
                $query = $this->connection->query("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.key_column_usage
                    WHERE TABLE_NAME = '".$this->getTable('entry_tag')."' AND CONSTRAINT_NAME LIKE 'FK_%'
                    AND TABLE_SCHEMA = '".$this->connection->getDatabase()."'"
                );
                $query->execute();

                foreach ($query->fetchAll() as $fk) {
                    $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' DROP FOREIGN KEY '.$fk['CONSTRAINT_NAME']);
                }
                break;

            case 'postgresql':
                // http://dba.stackexchange.com/questions/36979/retrieving-all-pk-and-fk
                $query = $this->connection->query("
                    SELECT conrelid::regclass AS table_from
                          ,conname
                          ,pg_get_constraintdef(c.oid)
                    FROM   pg_constraint c
                    JOIN   pg_namespace n ON n.oid = c.connamespace
                    WHERE  contype = 'f'
                    AND    conrelid::regclass::text = '".$this->getTable('entry_tag')."'
                    AND    n.nspname = 'public';"
                );
                $query->execute();

                foreach ($query->fetchAll() as $fk) {
                    $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' DROP CONSTRAINT '.$fk['conname']);
                }
                break;
        }

        $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' ADD CONSTRAINT FK_entry_tag_entry FOREIGN KEY (entry_id) REFERENCES '.$this->getTable('entry').' (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE '.$this->getTable('entry_tag').' ADD CONSTRAINT FK_entry_tag_tag FOREIGN KEY (tag_id) REFERENCES '.$this->getTable('tag').' (id) ON DELETE CASCADE');

        // remove entry FK from annotation

        switch ($this->connection->getDatabasePlatform()->getName()) {
            case 'mysql':
                $query = $this->connection->query("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.key_column_usage
                    WHERE TABLE_NAME = '".$this->getTable('annotation')."'
                    AND CONSTRAINT_NAME LIKE 'FK_%'
                    AND COLUMN_NAME = 'entry_id'
                    AND TABLE_SCHEMA = '".$this->connection->getDatabase()."'"
                );
                $query->execute();

                foreach ($query->fetchAll() as $fk) {
                    $this->addSql('ALTER TABLE '.$this->getTable('annotation').' DROP FOREIGN KEY '.$fk['CONSTRAINT_NAME']);
                }
                break;

            case 'postgresql':
                // http://dba.stackexchange.com/questions/36979/retrieving-all-pk-and-fk
                $query = $this->connection->query("
                    SELECT conrelid::regclass AS table_from
                          ,conname
                          ,pg_get_constraintdef(c.oid)
                    FROM   pg_constraint c
                    JOIN   pg_namespace n ON n.oid = c.connamespace
                    WHERE  contype = 'f'
                    AND    conrelid::regclass::text = '".$this->getTable('annotation')."'
                    AND    n.nspname = 'public'
                    AND    pg_get_constraintdef(c.oid) LIKE '%entry_id%';"
                );
                $query->execute();

                foreach ($query->fetchAll() as $fk) {
                    $this->addSql('ALTER TABLE '.$this->getTable('annotation').' DROP CONSTRAINT '.$fk['conname']);
                }
                break;
        }

        $this->addSql('ALTER TABLE '.$this->getTable('annotation').' ADD CONSTRAINT FK_annotation_entry FOREIGN KEY (entry_id) REFERENCES '.$this->getTable('entry').' (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        throw new SkipMigrationException('Too complex ...');
    }
}
