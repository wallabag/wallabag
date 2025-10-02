<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Wallabag\Doctrine\WallabagMigration;

/**
 * Add setting to enable or disable each importer.
 */
final class Version20250526113708 extends WallabagMigration
{
    private $settings = [
        [
            'name' => 'pocket_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'wallabag_v1_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'wallabag_v2_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'elcura_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'readability_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'instapaper_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'pinboard_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'delicious_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'omnivore_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'firefox_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'chrome_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'shaarli_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'pocket_html_enabled',
            'value' => '1',
            'section' => 'import',
        ],
        [
            'name' => 'elcurator_enabled',
            'value' => '1',
            'section' => 'import',
        ],
    ];

    public function up(Schema $schema): void
    {
        foreach ($this->settings as $setting) {
            $settingEnabled = $this->connection
                ->fetchOne('SELECT * FROM ' . $this->getTable('internal_setting') . " WHERE name = '" . $setting['name'] . "'");

            if (false !== $settingEnabled) {
                continue;
            }

            $this->addSql('INSERT INTO ' . $this->getTable('internal_setting') . " (name, value, section) VALUES ('" . $setting['name'] . "', '" . $setting['value'] . "', '" . $setting['section'] . "');");
        }
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(true, 'These settings are required and should not be removed.');
    }
}
