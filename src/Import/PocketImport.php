<?php

namespace Wallabag\Import;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Wallabag\Entity\Entry;

class PocketImport extends AbstractImport
{
    public const NB_ELEMENTS = 30;
    /**
     * @var HttpClientInterface
     */
    private $client;
    private $accessToken;

    /**
     * Only used for test purpose.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getName()
    {
        return 'Pocket';
    }

    public function getUrl()
    {
        return 'import_pocket';
    }

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
        try {
            $response = $this->client->request(Request::METHOD_POST, 'https://getpocket.com/v3/oauth/request', [
                'json' => [
                    'consumer_key' => $this->user->getConfig()->getPocketConsumerKey(),
                    'redirect_uri' => $redirectUri,
                ],
            ]);

            return $response->toArray()['code'];
        } catch (ExceptionInterface $e) {
            $this->logger->error(\sprintf('PocketImport: Failed to request token: %s', $e->getMessage()), ['exception' => $e]);

            return false;
        }
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
        try {
            $response = $this->client->request(Request::METHOD_POST, 'https://getpocket.com/v3/oauth/authorize', [
                'json' => [
                    'consumer_key' => $this->user->getConfig()->getPocketConsumerKey(),
                    'code' => $code,
                ],
            ]);

            $this->accessToken = $response->toArray()['access_token'];

            return true;
        } catch (ExceptionInterface $e) {
            $this->logger->error(\sprintf('PocketImport: Failed to authorize client: %s', $e->getMessage()), ['exception' => $e]);

            return false;
        }
    }

    public function import($offset = 0)
    {
        static $run = 0;

        try {
            $response = $this->client->request(Request::METHOD_POST, 'https://getpocket.com/v3/get', [
                'json' => [
                    'consumer_key' => $this->user->getConfig()->getPocketConsumerKey(),
                    'access_token' => $this->accessToken,
                    'detailType' => 'complete',
                    'state' => 'all',
                    'sort' => 'newest',
                    'count' => self::NB_ELEMENTS,
                    'offset' => $offset,
                ],
            ]);

            $entries = $response->toArray();

            if ($this->producer) {
                $this->parseEntriesForProducer($entries['list']);
            } else {
                $this->parseEntries($entries['list']);
            }

            // if we retrieve exactly the amount of items requested it means we can get more
            // re-call import and offset item by the amount previous received:
            //  - first call get 5k offset 0
            //  - second call get 5k offset 5k
            //  - and so on
            if (self::NB_ELEMENTS === \count($entries['list'])) {
                ++$run;

                return $this->import(self::NB_ELEMENTS * $run);
            }

            return true;
        } catch (ExceptionInterface $e) {
            $this->logger->error(\sprintf('PocketImport: Failed to import: %s', $e->getMessage()), ['exception' => $e]);

            return false;
        }
    }

    /**
     * Set the Http client.
     */
    public function setClient(HttpClientInterface $pocketClient)
    {
        $this->client = $pocketClient;
    }

    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['resolved_url']) && empty($importedEntry['given_url'])) {
            return false;
        }

        return true;
    }

    /**
     * @see https://getpocket.com/developer/docs/v3/retrieve
     */
    public function parseEntry(array $importedEntry)
    {
        $url = isset($importedEntry['resolved_url']) && '' !== $importedEntry['resolved_url'] ? $importedEntry['resolved_url'] : $importedEntry['given_url'];

        $existingEntry = $this->em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($url, $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return null;
        }

        $entry = new Entry($this->user);
        $entry->setUrl($url);

        // update entry with content (in case fetching failed, the given entry will be return)
        $this->fetchContent($entry, $url);

        // 0, 1, 2 - 1 if the item is archived - 2 if the item should be deleted
        $entry->updateArchived(1 === (int) $importedEntry['status'] || $this->markAsRead);

        // 0 or 1 - 1 if the item is starred
        $entry->setStarred(1 === (int) $importedEntry['favorite']);

        $title = 'Untitled';
        if (isset($importedEntry['resolved_title']) && '' !== $importedEntry['resolved_title']) {
            $title = $importedEntry['resolved_title'];
        } elseif (isset($importedEntry['given_title']) && '' !== $importedEntry['given_title']) {
            $title = $importedEntry['given_title'];
        }

        $entry->setTitle($title);

        // 0, 1, or 2 - 1 if the item has images in it - 2 if the item is an image
        if (isset($importedEntry['has_image']) && $importedEntry['has_image'] > 0 && isset($importedEntry['images'][1])) {
            $entry->setPreviewPicture($importedEntry['images'][1]['src']);
        }

        if (isset($importedEntry['tags']) && !empty($importedEntry['tags'])) {
            $this->tagsAssigner->assignTagsToEntry(
                $entry,
                array_keys($importedEntry['tags']),
                $this->em->getUnitOfWork()->getScheduledEntityInsertions()
            );
        }

        if (!empty($importedEntry['time_added'])) {
            $entry->setCreatedAt((new \DateTime())->setTimestamp($importedEntry['time_added']));
        }

        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }

    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['status'] = '1';

        return $importedEntry;
    }
}
