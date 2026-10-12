<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add PKCE support to OAuth2 implementation.
 *
 * Adds code_challenge and code_challenge_method fields to oauth2_auth_codes table
 * and client type fields to oauth2_clients table for OAuth 2.1 compliance.
 */
class Version20250703140000 extends WallabagMigration
{
    public function up(Schema $schema): void
    {
        $authCodeTable = $schema->getTable($this->getTable('oauth2_auth_codes'));
        $clientTable = $schema->getTable($this->getTable('oauth2_clients'));

        // Add PKCE fields to auth_codes table
        $this->skipIf($authCodeTable->hasColumn('code_challenge'), 'It seems that you already played this migration.');

        $authCodeTable->addColumn('code_challenge', 'string', [
            'length' => 128,
            'notnull' => false,
        ]);

        $authCodeTable->addColumn('code_challenge_method', 'string', [
            'length' => 10,
            'notnull' => false,
        ]);

        // Add client type fields to clients table
        $clientTable->addColumn('is_public', 'boolean', [
            'default' => false,
            'notnull' => true,
        ]);

        $clientTable->addColumn('require_pkce', 'boolean', [
            'default' => false,
            'notnull' => true,
        ]);
    }

    public function down(Schema $schema): void
    {
        $authCodeTable = $schema->getTable($this->getTable('oauth2_auth_codes'));
        $clientTable = $schema->getTable($this->getTable('oauth2_clients'));

        if ($authCodeTable->hasColumn('code_challenge')) {
            $authCodeTable->dropColumn('code_challenge');
        }

        if ($authCodeTable->hasColumn('code_challenge_method')) {
            $authCodeTable->dropColumn('code_challenge_method');
        }

        if ($clientTable->hasColumn('is_public')) {
            $clientTable->dropColumn('is_public');
        }

        if ($clientTable->hasColumn('require_pkce')) {
            $clientTable->dropColumn('require_pkce');
        }
    }
}
