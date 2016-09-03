<?php

namespace Wallabag\ImportBundle\Import;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Psr\Log\NullLogger;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Security\Core\User\UserInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Craue\ConfigBundle\Util\Config;

class PocketImport extends AbstractImport
{
    private $user;
    private $client;
    private $consumerKey;
    private $skippedEntries = 0;
    private $importedEntries = 0;
    private $markAsRead;
    private $producer;
    protected $accessToken;

    public function __construct(EntityManager $em, ContentProxy $contentProxy, Config $craueConfig)
    {
        $this->em = $em;
        $this->contentProxy = $contentProxy;
        $this->consumerKey = $craueConfig->get('pocket_consumer_key');
        $this->logger = new NullLogger();
    }

    /**
     * Set RabbitMQ Producer to send each entry to a queue.
     * This method should be called when user has enabled RabbitMQ.
     *
     * @param Producer $producer
     */
    public function setRabbitmqProducer(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * Set current user.
     * Could the current *connected* user or one retrieve by the consumer.
     *
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
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
     * @return string|false request_token for callback method
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

        if ($this->producer) {
            $this->parseEntriesForProducer($entries['list']);

            return true;
        }

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
     * @param array $entries
     */
    private function parseEntries(array $entries)
    {
        $i = 1;

        foreach ($entries as $pocketEntry) {
            $entry = $this->parseEntry($pocketEntry);

            if (null === $entry) {
                continue;
            }

            // flush every 20 entries
            if (($i % 20) === 0) {
                $this->em->flush();
                $this->em->clear($entry);
            }

            ++$i;
        }

        $this->em->flush();
    }

    public function parseEntry(array $pocketEntry)
    {
        $url = isset($pocketEntry['resolved_url']) && $pocketEntry['resolved_url'] != '' ? $pocketEntry['resolved_url'] : $pocketEntry['given_url'];

        $existingEntry = $this->em
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($url, $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return;
        }

        $entry = new Entry($this->user);
        $entry = $this->fetchContent($entry, $url);

        // jump to next entry in case of problem while getting content
        if (false === $entry) {
            ++$this->skippedEntries;

            return;
        }

        // 0, 1, 2 - 1 if the item is archived - 2 if the item should be deleted
        if ($pocketEntry['status'] == 1 || $this->markAsRead) {
            $entry->setArchived(true);
        }

        // 0 or 1 - 1 If the item is starred
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
        $entry->setUrl($url);

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

        return $entry;
    }

    /**
     * Faster parse entries for Producer.
     * We don't care to make check at this time. They'll be done by the consumer.
     *
     * @param array $entries
     */
    public function parseEntriesForProducer($entries)
    {
        foreach ($entries as $pocketEntry) {
            // set userId for the producer (it won't know which user is connected)
            $pocketEntry['userId'] = $this->user->getId();

            if ($this->markAsRead) {
                $pocketEntry['status'] = 1;
            }

            ++$this->importedEntries;

            $this->producer->publish(json_encode($pocketEntry));
        }
    }
}
