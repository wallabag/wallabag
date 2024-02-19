<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add updated_at fields to site_credential table.
 */
final class Version20190117131816 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $siteCredentialTable = $schema->getTable($this->getTable('site_credential'));

        $this->skipIf($siteCredentialTable->hasColumn('updated_at'), 'It seems that you already played this migration.');

        $siteCredentialTable->addColumn('updated_at', 'datetime', [
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        $siteCredentialTable = $schema->getTable($this->getTable('site_credential'));

        $this->skipIf(!$siteCredentialTable->hasColumn('updated_at'), 'It seems that you already played this migration.');

        $siteCredentialTable->dropColumn('updated_at');
    }
}
