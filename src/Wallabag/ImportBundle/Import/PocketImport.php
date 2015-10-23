<?php

namespace Wallabag\ImportBundle\Import;

use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Session\Session;
use Wallabag\CoreBundle\Entity\Entry;
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
     * @param $entries
     */
    private function parsePocketEntries($entries)
    {
        foreach ($entries as $entry) {
            $newEntry = new Entry($this->user);
            $newEntry->setUrl($entry['given_url']);
            $newEntry->setTitle(isset($entry['resolved_title']) ? $entry['resolved_title'] : (isset($entry['given_title']) ? $entry['given_title'] : 'Untitled'));

            if (isset($entry['excerpt'])) {
                $newEntry->setContent($entry['excerpt']);
            }

            if (isset($entry['has_image']) && $entry['has_image'] > 0) {
                $newEntry->setPreviewPicture($entry['image']['src']);
            }

            if (isset($entry['word_count'])) {
                $newEntry->setReadingTime(Utils::convertWordsToMinutes($entry['word_count']));
            }

            $this->em->persist($newEntry);
        }

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

        $request = $client->createRequest('POST', 'https://getpocket.com/v3/get',
            [
                'body' => json_encode([
                    'consumer_key' => $this->consumerKey,
                    'access_token' => $accessToken,
                    'detailType' => 'complete',
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
