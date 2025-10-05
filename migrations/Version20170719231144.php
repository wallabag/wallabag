<?php

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Changed tags to lowercase.
 */
class Version20170719231144 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof SqlitePlatform) {
            $this->write('Migration can only be executed safely on \'mysql\' or \'postgresql\'.');

            return;
        }

        // Find tags which need to be merged
        $dupTags = $this->connection->executeQuery('
            SELECT LOWER(label) AS lower_label
            FROM   ' . $this->getTable('tag') . '
            GROUP BY LOWER(label)
            HAVING COUNT(*) > 1'
        );

        foreach ($dupTags->fetchAllAssociative() as $duplicates) {
            $label = $duplicates['lower_label'];

            // Retrieve all duplicate tags for a given tag
            $tags = $this->connection->executeQuery('
                SELECT id
                FROM   ' . $this->getTable('tag') . '
                WHERE  LOWER(label) = :label
                ORDER BY id ASC',
                [
                    'label' => $label,
                ]
            );

            $first = true;
            $newId = null;
            $ids = [];

            foreach ($tags->fetchAllAssociative() as $tag) {
                // Ignore the first tag as we use it as the new reference tag
                if ($first) {
                    $first = false;
                    $newId = $tag['id'];
                } else {
                    $ids[] = $tag['id'];
                }
            }

            // Just in case...
            if (\count($ids) > 0) {
                // Merge tags
                $this->addSql('
                    UPDATE ' . $this->getTable('entry_tag') . '
                    SET    tag_id = ' . $newId . '
                    WHERE  tag_id IN (' . implode(',', $ids) . ')
                        AND entry_id NOT IN (
                           SELECT entry_id
                           FROM (SELECT * FROM ' . $this->getTable('entry_tag') . ') AS _entry_tag
                           WHERE tag_id = ' . $newId . '
                        )'
                );

                // Delete links to unused tags
                $this->addSql('
                    DELETE FROM ' . $this->getTable('entry_tag') . '
                    WHERE tag_id IN (' . implode(',', $ids) . ')'
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

    public function down(Schema $schema): void
    {
        throw new SkipMigrationException('Too complex ...');
    }
}
