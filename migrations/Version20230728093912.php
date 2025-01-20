<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add is_not_parsed field to entry table.
 */
final class Version20230728093912 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('is_not_parsed'), 'It seems that you already played this migration.');

        $entryTable->addColumn('is_not_parsed', 'boolean', [
            'default' => 0,
            'notnull' => false,
        ]);
    }

    /**
     * Query to update entries where content is equal to `fetching_error_message`.
     */
    public function postUp(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf(!$entryTable->hasColumn('is_not_parsed'), 'Unable to update is_not_parsed colum');

        // Need to do a `LIKE` with a final percent to handle the new line character
        $this->connection->executeQuery(
            'UPDATE ' . $this->getTable('entry') . ' SET is_not_parsed = :isNotParsed WHERE content LIKE :content',
            [
                'isNotParsed' => true,
                'content' => str_replace("\n", '', addslashes($this->fetchingErrorMessage)) . '%',
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $entryTable->dropColumn('is_not_parsed');
    }
}
