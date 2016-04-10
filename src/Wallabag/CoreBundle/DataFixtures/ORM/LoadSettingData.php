<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Craue\ConfigBundle\Entity\Setting;

class LoadSettingData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $settings = [
            [
                'name' => 'share_public',
                'value' => '1',
                'section' => 'entry',
            ],
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
                'name' => 'pocket_consumer_key',
                'value' => null,
                'section' => 'import',
            ],
            [
                'name' => 'show_printlink',
                'value' => '1',
                'section' => 'entry',
            ],
            [
                'name' => 'wallabag_support_url',
                'value' => 'https://www.wallabag.org/pages/support.html',
                'section' => 'misc',
            ],
            [
                'name' => 'wallabag_url',
                'value' => 'http://v2.wallabag.org',
                'section' => 'misc',
            ],
            [
                'name' => 'piwik_enabled',
                'value' => '0',
                'section' => 'analytics',
            ],
            [
                'name' => 'piwik_host',
                'value' => 'http://v2.wallabag.org',
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
        ];

        foreach ($settings as $setting) {
            $newSetting = new Setting();
            $newSetting->setName($setting['name']);
            $newSetting->setValue($setting['value']);
            $newSetting->setSection($setting['section']);
            $manager->persist($newSetting);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 50;
    }
}
