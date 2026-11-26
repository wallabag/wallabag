<?php

namespace Wallabag\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class IconExtension extends AbstractExtension
{
    private const ALIASES = [
        'smartphone' => 'device',
        'import_export' => 'import',
        'vpn_key' => 'key',
        'live_help' => 'help',
        'assistant_photo' => 'flag',
        'input' => 'logout',
        'people' => 'users',
        'build' => 'tool',
        'brightness_high' => 'sun',
        'brightness_low' => 'moon',
        'brightness_auto' => 'contrast',
        'arrow_back' => 'arrow-left',
        'casino' => 'shuffle',
        'error' => 'help',
        'filter_list' => 'filter',
        'file_download' => 'download',
        'rss_feed' => 'rss',
        'outbox' => 'unarchive',
        'star_outline' => 'star',
        'delete' => 'trash',
        'label_outline' => 'tag',
        'create' => 'edit',
        'launch' => 'external',
        'today' => 'calendar',
        'person' => 'user',
    ];
    private const PATHS = [
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'unread' => '<path d="M6 4h12v16l-6-4-6 4z"/>',
        'star' => '<path d="m12 3.5 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8L3.5 9.7l5.9-.9z"/>',
        'archive' => '<rect x="3" y="4" width="18" height="5" rx="1"/><path d="M5 9v10h14V9M10 13h4"/>',
        'all' => '<path d="M3 6h6l2 2h10v11H3z"/>',
        'annotated' => '<path d="m9 15 6-6 3 3-6 6H9z"/><path d="M4 20h7"/>',
        'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
        'feed' => '<path d="M5 5a14 14 0 0 1 14 14M5 11a8 8 0 0 1 8 8"/><circle cx="6" cy="18" r="1.2"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'shuffle' => '<path d="M4 7h3c5 0 5 10 10 10h3M4 17h3c1.5 0 2.5-.8 3.3-2M14 9c.8-1.2 1.8-2 3-2h3M18 4l3 3-3 3M18 14l3 3-3 3"/>',
        'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        'download' => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
        'settings' => '<path d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2"/><circle cx="10" cy="17" r="2"/>',
        'device' => '<rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/>',
        'import' => '<path d="M12 3v12M7 10l5 5 5-5M4 15v5h16v-5"/>',
        'key' => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M17 6l3 3"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M3 20c1-3.5 3.3-5 6-5s5 1.5 6 5M16 4.5a3.5 3.5 0 0 1 0 7M18 15c1.5.6 2.5 2.2 3 5"/>',
        'tool' => '<path d="M14.5 6.5a4 4 0 0 0 5 5L12 19a2.1 2.1 0 0 1-3-3z"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon' => '<path d="M20 14.5A8 8 0 0 1 9.5 4 8 8 0 1 0 20 14.5z"/>',
        'contrast' => '<circle cx="12" cy="12" r="9"/><path d="M12 3v18a9 9 0 0 0 0-18z" fill="currentColor"/>',
        'flag' => '<path d="M5 21V4h11l-1.5 4L16 12H5"/>',
        'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6V14M12 17.5v.01"/>',
        'book' => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M4 21V5M8 7h7"/>',
        'logout' => '<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"/>',
        'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'chevron-up' => '<path d="m6 15 6-6 6 6"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'link' => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
        'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'unarchive' => '<rect x="3" y="4" width="18" height="5" rx="1"/><path d="M5 9v10h14V9M12 18v-6M9.5 14.5 12 12l2.5 2.5"/>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/>',
        'list' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'grid' => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
        'select' => '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z"/>',
        'rss' => '<path d="M5 5a14 14 0 0 1 14 14M5 11a8 8 0 0 1 8 8"/><circle cx="6" cy="18" r="1.2"/>',
        'refresh' => '<path d="M20 11a8 8 0 0 0-14.6-4.5L4 8M4 4v4h4M4 13a8 8 0 0 0 14.6 4.5L20 16M20 20v-4h-4"/>',
        'print' => '<path d="M7 9V4h10v5M7 17H4v-7h16v7h-3M7 14h10v6H7z"/>',
        'share' => '<path d="M12 15V3M7 8l5-5 5 5M5 13v7h14v-7"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5H5V6h5"/>',
        'edit' => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="M3 3l18 18M10.6 5.1A10.6 10.6 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3 3.9M6.6 6.6C3.8 8.4 2 12 2 12s3.5 7 10 7c1.9 0 3.5-.5 4.9-1.3M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
        'check' => '<path d="m5 12 5 5L20 7"/>',
        'more' => '<circle cx="5" cy="12" r="1.4" fill="currentColor"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/><circle cx="19" cy="12" r="1.4" fill="currentColor"/>',
        'pen' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M4 10h16M8 3v4M16 3v4"/>',
        'comment' => '<path d="M4 5h16v11H9l-5 4z"/><path d="M8 9h8M8 12h5"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'chat' => '<path d="M4 5h16v11H9l-5 4z"/>',
    ];

    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', $this->icon(...)),
        ];
    }

    public function icon(string $name, int $size = 20, bool $filled = false): Markup
    {
        $key = self::ALIASES[$name] ?? $name;

        return new Markup(\sprintf(
            '<svg class="wb-icon" aria-hidden="true" focusable="false" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="%2$s" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">%3$s</svg>',
            $size,
            $filled ? 'currentColor' : 'none',
            self::PATHS[$key] ?? '',
        ), 'UTF-8');
    }
}
