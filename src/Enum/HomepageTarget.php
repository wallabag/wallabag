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
        return 'config.form_settings.default_homepage.' . $this->value;
    }
}
