<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add missing entries in craue_config_setting.
 */
final class Version20190129120000 extends WallabagMigration
{
    private $settings = [
        [
            'name' => 'carrot',
            'value' => '1',
            'section' => 'entry',
        ],
        [
            'name' => 'share_diaspora',
            'value' => '1',
            'section' => 'entry',
        ],
        [
            'name' => 'diaspora_url',
            'value' => 'http://diasporapod.com',
            'section' => 'entry',
        ],
        [
            'name' => 'share_shaarli',
            'value' => '1',
            'section' => 'entry',
        ],
        [
            'name' => 'shaarli_url',
            'value' => 'http://myshaarli.com',
            'section' => 'entry',
        ],
        [
            'name' => 'share_mail',
            'value' => '1',
            'section' => 'entry',
        ],
        [
            'name' => 'share_twitter',
            'value' => '1',
            'section' => 'entry',
        ],
        [
            'name' => 'show_printlink',
            'value' => '1',
            'section' => 'entry',
        ],
        [
            'name' => 'export_epub',
            'value' => '1',
            'section' => 'export',
        ],
        [
            'name' => 'export_mobi',
            'value' => '1',
            'section' => 'export',
        ],
        [
            'name' => 'export_pdf',
            'value' => '1',
            'section' => 'export',
        ],
        [
            'name' => 'export_csv',
            'value' => '1',
            'section' => 'export',
        ],
        [
            'name' => 'export_json',
            'value' => '1',
            'section' => 'export',
        ],
        [
            'name' => 'export_txt',
            'value' => '1',
            'section' => 'export',
        ],
        [
            'name' => 'export_xml',
            'value' => '1',
            'section' => 'export',
        ],
        [
            'name' => 'piwik_enabled',
            'value' => '0',
            'section' => 'analytics',
        ],
        [
            'name' => 'piwik_host',
            'value' => 'v2.wallabag.org',
            'section' => 'analytics',
        ],
        [
            'name' => 'piwik_site_id',
            'value' => '1',
            'section' => 'analytics',
        ],
        [
            'name' => 'demo_mode_enabled',
            'value' => '0',
            'section' => 'misc',
        ],
        [
            'name' => 'demo_mode_username',
            'value' => 'wallabag',
            'section' => 'misc',
        ],
        [
            'name' => 'wallabag_support_url',
            'value' => 'https://www.wallabag.org/pages/support.html',
            'section' => 'misc',
        ],
    ];

    public function up(Schema $schema): void
    {
        foreach ($this->settings as $setting) {
            $settingEnabled = $this->connection
                ->fetchOne('SELECT * FROM ' . $this->getTable('craue_config_setting') . " WHERE name = '" . $setting['name'] . "'");

            if (false !== $settingEnabled) {
                continue;
            }

            $this->addSql('INSERT INTO ' . $this->getTable('craue_config_setting') . " (name, value, section) VALUES ('" . $setting['name'] . "', '" . $setting['value'] . "', '" . $setting['section'] . "');");
        }
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(true, 'These settings are required and should not be removed.');
    }
}
