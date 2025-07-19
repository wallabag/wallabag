<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add boolean for two-step setup for google authenticator
 */
final class Version20250413133131 extends WallabagMigration
{
    
    public function up(Schema $schema): void
    {
        $userTable = $schema->getTable($this->getTable('user'));

        $this->skipIf($userTable->hasColumn('google_authenticator'), 'It seems that you already played this migration.');

        $userTable->addColumn('google_authenticator', 'boolean', [
            'default' => false,
            'notnull' => true,
        ]);
    }

    public function down(Schema $schema): void
    {
        $userTable = $schema->getTable($this->getTable('user'));
        $userTable->dropColumn('google_authenticator');
    }
}
