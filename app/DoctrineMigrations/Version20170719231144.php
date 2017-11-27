<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Changed tags to lowercase.
 */
class Version20170719231144 extends AbstractMigration implements ContainerAwareInterface
{
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
        $this->skipIf('sqlite' === $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

        // Find tags which need to be merged
        $dupTags = $this->connection->query('
            SELECT LOWER(label)
            FROM   ' . $this->getTable('tag') . '
            GROUP BY LOWER(label)
            HAVING COUNT(*) > 1'
        );
        $dupTags->execute();

        foreach ($dupTags->fetchAll() as $duplicates) {
            $label = $duplicates['LOWER(label)'];

            // Retrieve all duplicate tags for a given tag
            $tags = $this->connection->createQuery('
                SELECT id
                FROM   ' . $this->getTable('tag') . "
                WHERE  LOWER(label) = :label
                ORDER BY id ASC"
            );
            $tags->setParameter('label', $label);
            $tags->execute();

            $first = true;
            $newId = null;
            $ids = [];

            foreach ($tags->fetchAll() as $tag) {
                // Ignore the first tag as we use it as the new reference tag
                if ($first) {
                    $first = false;
                    $newId = $tag['id'];
                } else {
                    $ids[] = $tag['id'];
                }
            }

            // Just in case...
            if (count($ids) > 0) {
                // Merge tags
                $this->addSql('
                    UPDATE ' . $this->getTable('entry_tag') . '
                    SET    tag_id = ' . $newId . '
                    WHERE  tag_id IN (' . implode(',', $ids) . ')'
                );

                // Delete unused tags
                $this->addSql('
                    DELETE FROM ' . $this->getTable('tag') . '
                    WHERE id IN (' . implode(',', $ids) . ')'
                );
            }
        }

        // Iterate over all tags to lowercase them
        $this->addSql('
            UPDATE ' . $this->getTable('tag') . '
            SET label = LOWER(label)'
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        throw new SkipMigrationException('Too complex ...');
    }

    private function getTable($tableName)
    {
        return $this->container->getParameter('database_table_prefix') . $tableName;
    }
}
