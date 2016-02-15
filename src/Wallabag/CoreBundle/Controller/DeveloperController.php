<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DeveloperController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/developer", name="developer")
     */
    public function indexAction(Request $request)
    {
        return $this->render('WallabagCoreBundle:Developer:index.html.twig');
    }

    /**
     * @param Request $request
     *
     * @Route("/developer/client/create", name="create_client")
     */
    public function createClientAction(Request $request)
    {
        $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();
        $client->setRedirectUris(array('http://www.example.com'));
        $client->setAllowedGrantTypes(array('token', 'authorization_code'));
        $clientManager->updateClient($client);

        return $this->render('WallabagCoreBundle:Developer:client.html.twig', array(
            'client_id' => $client->getPublicId(),
        ));
    }
}
