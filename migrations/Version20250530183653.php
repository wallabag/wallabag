<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add the entry deletion table to keep a history of deleted entries.
 */
final class Version20250530183653 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE "wallabag_entry_deletion" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, entry_id INTEGER NOT NULL, deleted_at DATETIME NOT NULL, CONSTRAINT FK_D91765D5A76ED395 FOREIGN KEY (user_id) REFERENCES "wallabag_user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D91765D5A76ED395 ON "wallabag_entry_deletion" (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D91765D54AF38FD1 ON "wallabag_entry_deletion" (deleted_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D91765D5A76ED3954AF38FD1 ON "wallabag_entry_deletion" (user_id, deleted_at)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE "wallabag_entry_deletion"
        SQL);
    }
}
