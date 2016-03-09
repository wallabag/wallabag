<?php

namespace Wallabag\ImportBundle\Import;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Craue\ConfigBundle\Util\Config;

class PocketImport implements ImportInterface
{
    private $user;
    private $em;
    private $contentProxy;
    private $logger;
    private $client;
    private $consumerKey;
    private $skippedEntries = 0;
    private $importedEntries = 0;
    private $markAsRead;
    protected $accessToken;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManager $em, ContentProxy $contentProxy, Config $craueConfig)
    {
        $this->user = $tokenStorage->getToken()->getUser();
        $this->em = $em;
        $this->contentProxy = $contentProxy;
        $this->consumerKey = $craueConfig->get('pocket_consumer_key');
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Pocket';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_pocket';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.pocket.description';
    }

    /**
     * Return the oauth url to authenticate the client.
     *
     * @param string $redirectUri Redirect url in case of error
     *
     * @return string request_token for callback method
     */
    public function getRequestToken($redirectUri)
    {
        $request = $this->client->createRequest('POST', 'https://getpocket.com/v3/oauth/request',
            [
                'body' => json_encode([
                    'consumer_key' => $this->consumerKey,
                    'redirect_uri' => $redirectUri,
                ]),
            ]
        );

        try {
            $response = $this->client->send($request);
        } catch (RequestException $e) {
            $this->logger->error(sprintf('PocketImport: Failed to request token: %s', $e->getMessage()), ['exception' => $e]);

            return false;
        }

        return $response->json()['code'];
    }

    /**
     * Usually called by the previous callback to authorize the client.
     * Then it return a token that can be used for next requests.
     *
     * @param string $code request_token from getRequestToken
     *
     * @return bool
     */
    public function authorize($code)
    {
        $request = $this->client->createRequest('POST', 'https://getpocket.com/v3/oauth/authorize',
            [
                'body' => json_encode([
                    'consumer_key' => $this->consumerKey,
                    'code' => $code,
                ]),
            ]
        );

        try {
            $response = $this->client->send($request);
        } catch (RequestException $e) {
            $this->logger->error(sprintf('PocketImport: Failed to authorize client: %s', $e->getMessage()), ['exception' => $e]);

            return false;
        }

        $this->accessToken = $response->json()['access_token'];

        return true;
    }

    /**
     * Set whether articles must be all marked as read.
     *
     * @param bool $markAsRead
     */
    public function setMarkAsRead($markAsRead)
    {
        $this->markAsRead = $markAsRead;

        return $this;
    }

    /**
     * Get whether articles must be all marked as read.
     */
    public function getMarkAsRead()
    {
        return $this->markAsRead;
    }

    /**
     * {@inheritdoc}
     */
    public function import()
    {
        $request = $this->client->createRequest('POST', 'https://getpocket.com/v3/get',
            [
                'body' => json_encode([
                    'consumer_key' => $this->consumerKey,
                    'access_token' => $this->accessToken,
                    'detailType' => 'complete',
                    'state' => 'all',
                    'sort' => 'oldest',
                ]),
            ]
        );

        try {
            $response = $this->client->send($request);
        } catch (RequestException $e) {
            $this->logger->error(sprintf('PocketImport: Failed to import: %s', $e->getMessage()), ['exception' => $e]);

            return false;
        }

        $entries = $response->json();

        $this->parseEntries($entries['list']);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        return [
            'skipped' => $this->skippedEntries,
            'imported' => $this->importedEntries,
        ];
    }

    /**
     * Set the Guzzle client.
     *
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @see https://getpocket.com/developer/docs/v3/retrieve
     *
     * @param $entries
     */
    private function parseEntries($entries)
    {
        $i = 1;

        foreach ($entries as $pocketEntry) {
            $url = isset($pocketEntry['resolved_url']) && $pocketEntry['resolved_url'] != '' ? $pocketEntry['resolved_url'] : $pocketEntry['given_url'];

            $existingEntry = $this->em
                ->getRepository('WallabagCoreBundle:Entry')
                ->findByUrlAndUserId($url, $this->user->getId());

            if (false !== $existingEntry) {
                ++$this->skippedEntries;
                continue;
            }

            $entry = new Entry($this->user);
            $entry = $this->contentProxy->updateEntry($entry, $url);

            // 0, 1, 2 - 1 if the item is archived - 2 if the item should be deleted
            if ($pocketEntry['status'] == 1 || $this->markAsRead) {
                $entry->setArchived(true);
            }

            // 0 or 1 - 1 If the item is favorited
            if ($pocketEntry['favorite'] == 1) {
                $entry->setStarred(true);
            }

            $title = 'Untitled';
            if (isset($pocketEntry['resolved_title']) && $pocketEntry['resolved_title'] != '') {
                $title = $pocketEntry['resolved_title'];
            } elseif (isset($pocketEntry['given_title']) && $pocketEntry['given_title'] != '') {
                $title = $pocketEntry['given_title'];
            }

            $entry->setTitle($title);

            // 0, 1, or 2 - 1 if the item has images in it - 2 if the item is an image
            if (isset($pocketEntry['has_image']) && $pocketEntry['has_image'] > 0 && isset($pocketEntry['images'][1])) {
                $entry->setPreviewPicture($pocketEntry['images'][1]['src']);
            }

            if (isset($pocketEntry['tags']) && !empty($pocketEntry['tags'])) {
                $this->contentProxy->assignTagsToEntry(
                    $entry,
                    array_keys($pocketEntry['tags'])
                );
            }

            $this->em->persist($entry);
            ++$this->importedEntries;

            // flush every 20 entries
            if (($i % 20) === 0) {
                $this->em->flush();
            }
            ++$i;
        }

        $this->em->flush();
    }
}
