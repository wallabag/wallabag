<?php

/** Offline replay of a public provider receipt through the real application sanitizer. */
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/AppKernel.php';
if (3 !== count($argv) || !preg_match('/^\d{1,25}$/', $argv[2])) {
    throw new InvalidArgumentException('Usage: php scripts/verify-x-article.php receipt.json post-id');
}
$payload = file_get_contents($argv[1]);
$data = json_decode($payload, true, 64, \JSON_THROW_ON_ERROR);
if (($data['status']['id'] ?? '') !== $argv[2]) {
    throw new RuntimeException('Post receipt ID mismatch.');
}
$kernel = new AppKernel('test', true);
$kernel->boot();
$http = new class($payload, $argv[2]) extends Wallabag\CoreBundle\Helper\PublicHttpClient {
    private $payload;
    private $id;

    public function __construct(string $payload, string $id)
    {
        $this->payload = $payload;
        $this->id = $id;
    }

    public function get(string $url): array
    {
        if ('https://api.fxtwitter.com/2/status/' . $this->id !== $url) {
            throw new RuntimeException('Unexpected outbound URL during offline replay.');
        }

        return ['body' => $this->payload];
    }
};
$container = $kernel->getContainer();
$container->set(Wallabag\CoreBundle\Helper\PublicHttpClient::class, $http);
$em = $container->get(Doctrine\ORM\EntityManagerInterface::class);
$user = $em->getRepository(Wallabag\UserBundle\Entity\User::class)->findOneByUsername('admin');
$user->getConfig()->setXArticleReaderEnabled(true);
$entry = new Wallabag\CoreBundle\Entity\Entry($user);
$container->get(Wallabag\CoreBundle\Helper\ContentProxy::class)->updateEntry($entry, 'https://x.com/i/status/' . $argv[2]);
$normalize = static function (string $text): string { return trim(preg_replace('/\s+/u', ' ', $text)); };
$plain = $normalize(html_entity_decode(strip_tags($entry->getContent()), \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
$blocks = $data['status']['article']['content']['blocks'];
$checked = 0;
foreach ($blocks as $block) {
    $text = $normalize($block['text']);
    if ('' !== $text) {
        ++$checked;
        if (false === strpos($plain, $text)) {
            throw new RuntimeException('A source body block was lost in sanitization.');
        }
    }
}
file_put_contents(__DIR__ . '/../.validation/real-article.html', $entry->getContent());
$report = ['post_id' => $argv[2], 'article_id' => $data['status']['article']['id'], 'title' => $entry->getTitle(), 'source_blocks' => count($blocks), 'nonempty_blocks_verified' => $checked, 'http_status' => (int) $entry->getHttpStatus(), 'all_nonempty_blocks_preserved' => true, 'provider_response_sha256' => hash('sha256', $payload), 'sanitized_html_sha256' => hash('sha256', $entry->getContent()), 'closing_paragraph_sha256' => hash('sha256', end($blocks)['text']), 'network_access_during_replay' => false, 'database_writes' => false];
file_put_contents(__DIR__ . '/../.validation/x-article-evidence.json', json_encode($report, \JSON_PRETTY_PRINT) . "\n");
echo json_encode($report) . "\n";
$kernel->shutdown();
