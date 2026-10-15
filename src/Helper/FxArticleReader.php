<?php

namespace Wallabag\Helper;

/** Converts public structured article blocks. Never accepts provider HTML or fetches media. */
class FxArticleReader
{
    private PublicHttpClient $http;

    public function __construct(PublicHttpClient $http)
    {
        $this->http = $http;
    }

    public function extract(string $canonical): array
    {
        $id = basename($canonical);
        if (!preg_match('/^\d{1,25}$/', $id)) {
            throw new \InvalidArgumentException('Invalid post ID.');
        }
        $response = $this->http->get('https://api.fxtwitter.com/2/status/' . $id);
        $data = json_decode($response['body'], true, 64, \JSON_THROW_ON_ERROR);
        $post = $data['status'] ?? null;
        if (200 !== ($data['code'] ?? null) || !\is_array($post) || ($post['id'] ?? null) !== $id
            || 'tombstone' === ($post['type'] ?? '') || true === ($post['author']['protected'] ?? false)) {
            throw new \RuntimeException('Public article is unavailable.');
        }

        return $this->render($post, $canonical);
    }

    public function render(array $post, string $canonical): array
    {
        $article = $post['article'] ?? [];
        $blocks = $article['content']['blocks'] ?? null;
        $title = $article['title'] ?? null;
        if (!\is_string($title) || '' === trim($title) || !\is_array($blocks) || !$blocks || \count($blocks) > 5000) {
            throw new \RuntimeException('Article body is missing or invalid.');
        }
        $entities = [];
        foreach (($article['content']['entityMap'] ?? []) as $key => $entity) {
            if (isset($entity['key'], $entity['value'])) {
                $entities[(string) $entity['key']] = $entity['value'];
            } elseif (\is_array($entity)) {
                $entities[(string) $key] = $entity;
            }
        }
        $media = [];
        foreach (($article['media_entities'] ?? []) as $item) {
            $media[(string) ($item['media_id'] ?? '')] = $item;
        }
        $author = $post['author']['name'] ?? 'Unknown author';
        if (!\is_string($author)) {
            $author = 'Unknown author';
        }
        $html = '<p>Article text captured from public X data via <a href="https://docs.fxembed.com/api/introduction/" rel="noreferrer noopener">FxTwitter</a>. External images and embeds are linked; they are not archived. Verify the original for later edits.</p>';
        $complete = true;
        $cover = $article['cover_media']['media_info']['original_img_url'] ?? null;
        if (\is_string($cover) && 'pbs.twimg.com' === parse_url($cover, \PHP_URL_HOST) && $this->safeLink($cover)) {
            $html .= '<p>' . $this->link($cover, 'Cover image') . '</p>';
        }
        $textLength = 0;
        $openList = null;
        foreach ($blocks as $block) {
            if (!\is_array($block) || !\is_string($block['text'] ?? null)) {
                throw new \RuntimeException('Malformed article block.');
            }
            $textLength += mb_strlen($block['text']);
            $type = $block['type'] ?? 'unstyled';
            $list = ['ordered-list-item' => 'ol', 'unordered-list-item' => 'ul'][$type] ?? null;
            if ($openList && $openList !== $list) {
                $html .= '</' . $openList . '>';
                $openList = null;
            }
            if ($list && !$openList) {
                $html .= '<' . $list . '>';
                $openList = $list;
            }
            if ('atomic' === $type) {
                $rendered = false;
                foreach (($block['entityRanges'] ?? []) as $range) {
                    $entity = $entities[(string) ($range['key'] ?? '')] ?? [];
                    $entityData = $entity['data'] ?? [];
                    if ('MEDIA' === ($entity['type'] ?? '')) {
                        $caption = \is_string($entityData['caption'] ?? null) ? $entityData['caption'] : 'Article media';
                        foreach (($entityData['mediaItems'] ?? []) as $ref) {
                            $item = $media[(string) ($ref['mediaId'] ?? '')] ?? [];
                            $url = $item['media_info']['original_img_url'] ?? '';
                            if (\is_string($url) && 'pbs.twimg.com' === parse_url($url, \PHP_URL_HOST) && $this->safeLink($url)) {
                                $html .= '<p>' . $this->link($url, $caption) . '</p>';
                                $rendered = true;
                            } else {
                                $complete = false;
                            }
                        }
                    } else {
                        $url = $entityData['url'] ?? null;
                        $tweetId = $entityData['tweetId'] ?? $entityData['tweet_id'] ?? $entityData['id'] ?? null;
                        if (!\is_string($url) && \is_string($tweetId) && preg_match('/^\d+$/', $tweetId)) {
                            $url = 'https://x.com/i/status/' . $tweetId;
                        }
                        if (\is_string($url) && XPostExtractor::canonicalUrl($url)) {
                            $html .= '<p>' . $this->link($url, 'Embedded X post') . '</p>';
                            $rendered = true;
                        }
                    }
                }
                if (!$rendered) {
                    $html .= '<p>[Embedded content unavailable in this snapshot]</p>';
                    $complete = false;
                }
                // Keep any textual atomic content instead of silently dropping it.
                if (trim($block['text'])) {
                    $html .= '<p>' . self::escape($block['text']) . '</p>';
                }
                continue;
            }
            $tag = ['unstyled' => 'p', 'paragraph' => 'p', 'header-one' => 'h2', 'header-two' => 'h3', 'header-three' => 'h4', 'blockquote' => 'blockquote', 'code-block' => 'pre', 'ordered-list-item' => 'li', 'unordered-list-item' => 'li'][$type] ?? 'p';
            if (!\in_array($type, ['unstyled', 'paragraph', 'header-one', 'header-two', 'header-three', 'blockquote', 'code-block', 'ordered-list-item', 'unordered-list-item'], true)) {
                $complete = false;
            }
            $body = $this->inline($block, $entities);
            $element = '<' . $tag . '>' . $body . '</' . $tag . '>';

            $html .= $element;
        }
        if ($openList) {
            $html .= '</' . $openList . '>';
        }
        if (0 === $textLength) {
            throw new \RuntimeException('Article has no body text.');
        }
        if (!$complete) {
            $html = '<p><strong>Partial article snapshot.</strong> Some embedded content or formatting is unavailable.</p>' . $html;
        }
        $handle = $post['author']['screen_name'] ?? '';
        $authorHtml = \is_string($handle) && preg_match('/^[A-Za-z0-9_]{1,15}$/', $handle) ? $this->link('https://x.com/' . $handle, $author . ' (@' . $handle . ')') : self::escape($author);
        $html .= '<p>By ' . $authorHtml . '. ' . $this->link($canonical, 'Original post and article on X') . '</p>';

        return ['title' => $title, 'html' => $html, 'url' => $canonical, 'status' => $complete ? 200 : 206, 'authors' => [$author], 'x_capture_type' => 'article', 'date' => \is_string($article['created_at'] ?? null) ? $article['created_at'] : (\is_string($post['created_at'] ?? null) ? $post['created_at'] : null)];
    }

    private function inline(array $block, array $entities): string
    {
        $utf16 = mb_convert_encoding($block['text'], 'UTF-16LE', 'UTF-8');
        $length = \strlen($utf16) / 2;
        $ranges = [];
        foreach (\array_slice($block['inlineStyleRanges'] ?? [], 0, 1000) as $range) {
            $style = strtolower($range['style'] ?? '');
            if (\in_array($style, ['bold', 'italic', 'strikethrough', 'code'], true)) {
                $range['tag'] = ['bold' => 'strong', 'italic' => 'em', 'strikethrough' => 's', 'code' => 'code'][$style];
                $ranges[] = $range;
            }
        }
        foreach (\array_slice($block['entityRanges'] ?? [], 0, 1000) as $range) {
            $entity = $entities[(string) ($range['key'] ?? '')] ?? [];
            if ('LINK' === ($entity['type'] ?? '') && $this->safeLink($entity['data']['url'] ?? '')) {
                $range['href'] = $entity['data']['url'];
                $ranges[] = $range;
            }
        }
        $ranges = array_values(array_filter($ranges, static function ($r) use ($length) { return \is_int($r['offset'] ?? null) && \is_int($r['length'] ?? null) && $r['offset'] >= 0 && $r['length'] > 0 && $r['offset'] + $r['length'] <= $length; }));
        $boundaries = [0, (int) $length];
        foreach ($ranges as $r) {
            $boundaries[] = $r['offset'];
            $boundaries[] = $r['offset'] + $r['length'];
        }
        $boundaries = array_values(array_unique($boundaries));
        sort($boundaries);
        $html = '';
        for ($i = 0; $i < \count($boundaries) - 1; ++$i) {
            $start = $boundaries[$i];
            $end = $boundaries[$i + 1];
            $part = nl2br(self::escape(mb_convert_encoding(substr($utf16, $start * 2, ($end - $start) * 2), 'UTF-8', 'UTF-16LE')));
            $href = null;
            foreach ($ranges as $r) {
                if ($r['offset'] <= $start && $r['offset'] + $r['length'] >= $end) {
                    if (isset($r['tag'])) {
                        $part = '<' . $r['tag'] . '>' . $part . '</' . $r['tag'] . '>';
                    }
                    if (isset($r['href'])) {
                        $href = $r['href'];
                    }
                }
            }
            if ($href) {
                $part = '<a href="' . self::escape($href) . '" rel="noreferrer noopener">' . $part . '</a>';
            }
            $html .= $part;
        }

        return $html;
    }

    private function safeLink($url): bool
    {
        if (!\is_string($url) || \strlen($url) > 2048 || preg_match('/[\x00-\x20\x7f]/', $url)) {
            return false;
        }
        $p = parse_url($url);

        return $p && \in_array($p['scheme'] ?? '', ['http', 'https'], true) && !empty($p['host']) && !isset($p['user']) && !isset($p['pass']);
    }

    private function link(string $url, string $text): string
    {
        return '<a href="' . self::escape($url) . '" rel="noreferrer noopener">' . self::escape($text) . '</a>';
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
    }
}
