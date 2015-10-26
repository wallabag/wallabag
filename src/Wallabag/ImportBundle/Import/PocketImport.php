<?php

namespace Wallabag\ImportBundle\Import;

use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Session\Session;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Tools\Utils;

class PocketImport implements ImportInterface
{
    private $user;
    private $session;
    private $em;
    private $consumerKey;

    public function __construct($tokenStorage, Session $session, EntityManager $em, $consumerKey)
    {
        $this->user = $tokenStorage->getToken()->getUser();
        $this->session = $session;
        $this->em = $em;
        $this->consumerKey = $consumerKey;
    }

    public function getName()
    {
        return 'Pocket';
    }

    public function getDescription()
    {
        return 'This importer will import all your <a href="https://getpocket.com">Pocket</a> data.';
    }

    /**
     * Create a new Client.
     *
     * @return Client
     */
    private function createClient()
    {
        return new Client([
            'defaults' => [
                'headers' => [
                    'content-type' => 'application/json',
                    'X-Accept' => 'application/json',
                ],
            ],
        ]);
    }

    /**
     * Returns the good title for current entry.
     *
     * @param $pocketEntry
     *
     * @return string
     */
    private function guessTitle($pocketEntry)
    {
        if (isset($pocketEntry['resolved_title']) && $pocketEntry['resolved_title'] != '') {
            return $pocketEntry['resolved_title'];
        } elseif (isset($pocketEntry['given_title']) && $pocketEntry['given_title'] != '') {
            return $pocketEntry['given_title'];
        } else {
            return 'Untitled';
        }
    }

    private function assignTagsToEntry(Entry $entry, $tags)
    {
        foreach ($tags as $tag) {
            $label = trim($tag['tag']);
            $tagEntity = $this->em
                ->getRepository('WallabagCoreBundle:Tag')
                ->findOneByLabelAndUserId($label, $this->user->getId());

            if (is_object($tagEntity)) {
                $entry->addTag($tagEntity);
            } else {
                $newTag = new Tag($this->user);
                $newTag->setLabel($label);
                $entry->addTag($newTag);
            }
            $this->em->flush();
        }
    }

    /**
     * @param $entries
     */
    private function parsePocketEntries($entries)
    {
        foreach ($entries as $pocketEntry) {
            $entry = new Entry($this->user);
            $entry->setUrl($pocketEntry['given_url']);
            if ($pocketEntry['status'] == 1) {
                $entry->setArchived(true);
            }
            if ($pocketEntry['favorite'] == 1) {
                $entry->setStarred(true);
            }

            $entry->setTitle($this->guessTitle($pocketEntry));

            if (isset($pocketEntry['excerpt'])) {
                $entry->setContent($pocketEntry['excerpt']);
            }

            if (isset($pocketEntry['has_image']) && $pocketEntry['has_image'] > 0) {
                $entry->setPreviewPicture($pocketEntry['image']['src']);
            }

            if (isset($pocketEntry['word_count'])) {
                $entry->setReadingTime(Utils::convertWordsToMinutes($pocketEntry['word_count']));
            }

            if (!empty($pocketEntry['tags'])) {
                $this->assignTagsToEntry($entry, $pocketEntry['tags']);
            }

            $this->em->persist($entry);
        }

        $this->user->setLastPocketImport(new \DateTime());
        $this->em->flush();
    }

    public function oAuthRequest($redirectUri, $callbackUri)
    {
        $client = $this->createClient();
        $request = $client->createRequest('POST', 'https://getpocket.com/v3/oauth/request',
            [
                'body' => json_encode([
                    'consumer_key' => $this->consumerKey,
                    'redirect_uri' => $redirectUri,
                ]),
            ]
        );

        $response = $client->send($request);
        $values = $response->json();

        // store code in session for callback method
        $this->session->set('pocketCode', $values['code']);

        return 'https://getpocket.com/auth/authorize?request_token='.$values['code'].'&redirect_uri='.$callbackUri;
    }

    public function oAuthAuthorize()
    {
        $client = $this->createClient();

        $request = $client->createRequest('POST', 'https://getpocket.com/v3/oauth/authorize',
            [
                'body' => json_encode([
                    'consumer_key' => $this->consumerKey,
                    'code' => $this->session->get('pocketCode'),
                ]),
            ]
        );

        $response = $client->send($request);

        return $response->json()['access_token'];
    }

    public function import($accessToken)
    {
        $client = $this->createClient();
        $since = (!is_null($this->user->getLastPocketImport()) ? $this->user->getLastPocketImport()->getTimestamp() : '');

        $request = $client->createRequest('POST', 'https://getpocket.com/v3/get',
            [
                'body' => json_encode([
                    'consumer_key' => $this->consumerKey,
                    'access_token' => $accessToken,
                    'detailType' => 'complete',
                    'state' => 'all',
                    'sort' => 'oldest',
                    'since' => $since,
                ]),
            ]
        );

        $response = $client->send($request);
        $entries = $response->json();

        $this->parsePocketEntries($entries['list']);

        $this->session->getFlashBag()->add(
            'notice',
            count($entries['list']).' entries imported'
        );
    }
}
