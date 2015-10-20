<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Tools\Utils;

class PocketController extends Controller
{
    /**
     * @Route("/import", name="import")
     */
    public function importAction()
    {
        return $this->render('WallabagImportBundle:Pocket:index.html.twig', array());
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
     * @Route("/auth-pocket", name="authpocket")
     */
    public function authAction()
    {
        $client = $this->createClient();
        $request = $client->createRequest('POST', 'https://getpocket.com/v3/oauth/request',
            [
                'body' => json_encode([
                    'consumer_key' => $this->container->getParameter('pocket_consumer_key'),
                    'redirect_uri' => $this->generateUrl('import', array(), true),
                ]),
            ]
        );

        $response = $client->send($request);
        $values = $response->json();
        $code = $values['code'];

        // store code in session for callback method
        $session = $this->get('session');
        $session->set('pocketCode',  $code);

        $url = 'https://getpocket.com/auth/authorize?request_token='.$code.'&redirect_uri='.$this->generateUrl('callbackpocket', array(), true);

        return $this->redirect($url, 301);
    }

    /**
     * @Route("/callback-pocket", name="callbackpocket")
     */
    public function callbackAction()
    {
        $client = $this->createClient();

        $request = $client->createRequest('POST', 'https://getpocket.com/v3/oauth/authorize',
            [
                'body' => json_encode([
                    'consumer_key' => $this->container->getParameter('pocket_consumer_key'),
                    'code' => $this->get('session')->get('pocketCode'),
                ]),
            ]
        );

        $response = $client->send($request);
        $values = $response->json();
        $accessToken = $values['access_token'];

        $request = $client->createRequest('POST', 'https://getpocket.com/v3/get',
            [
                'body' => json_encode([
                    'consumer_key' => $this->container->getParameter('pocket_consumer_key'),
                    'access_token' => $accessToken,
                    'detailType' => 'complete',
                ]),
            ]
        );

        $response = $client->send($request);
        $entries = $response->json();

        $this->parsePocketEntries($entries['list']);

        $this->get('session')->getFlashBag()->add(
            'notice',
            count($entries['list']).' entries imported'
        );

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @param $entries
     */
    private function parsePocketEntries($entries)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($entries as $entry) {
            $newEntry = new Entry($this->getUser());
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

            $em->persist($newEntry);
        }

        $em->flush();
    }
}
