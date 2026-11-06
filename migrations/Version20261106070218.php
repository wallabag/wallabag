<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Clean entries saved with empty authors (see #8935).
 */
final class Version20261106070218 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'UPDATE ' . $this->getTable('entry') . ' SET published_by = NULL WHERE published_by IN (:emptyArray, :emptyAuthor)',
            [
                'emptyArray' => serialize([]),
                'emptyAuthor' => serialize(['']),
            ]
        );
    }

    public function down(Schema $schema): void
    {
        // Nothing to restore
    }
}
