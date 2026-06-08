<?php

namespace Wallabag\Enum;

enum HomepageTarget: string
{
    case Unread = 'unread';
    case All = 'all';
    case Archive = 'archive';
    case Starred = 'starred';
    case Tags = 'tags';

    public function label(): string
    {
        return match ($this) {
            self::Unread => 'config.form_settings.default_homepage.unread',
            self::All => 'config.form_settings.default_homepage.all',
            self::Archive => 'config.form_settings.default_homepage.archive',
            self::Starred => 'config.form_settings.default_homepage.starred',
            self::Tags => 'config.form_settings.default_homepage.tags',
        };
    }
}
