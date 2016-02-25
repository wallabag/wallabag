<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\ApiBundle\Entity\Client;
use Wallabag\CoreBundle\Form\Type\ClientType;

class DeveloperController extends Controller
{
    /**
     * @Route("/developer", name="developer")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('WallabagCoreBundle:Developer:index.html.twig');
    }

    /**
     * @param Request $request
     *
     * @Route("/developer/client/create", name="create_client")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createClientAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $client = new Client();
        $clientForm = $this->createForm(ClientType::class, $client);
        $clientForm->handleRequest($request);

        if ($clientForm->isValid()) {
            $client->setAllowedGrantTypes(array('token', 'authorization_code','password'));
            $em->persist($client);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'New client created.'
            );

            return $this->render('WallabagCoreBundle:Developer:client_parameters.html.twig', array(
                'client_id' => $client->getPublicId(),
                'client_secret' => $client->getSecret(),
            ));
        }

        return $this->render('WallabagCoreBundle:Developer:client.html.twig', array(
            'form' => $clientForm->createView(),
        ));
    }

    /**
     * @Route("/developer/howto/first-app", name="howto-firstapp")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function howtoFirstAppAction()
    {
        return $this->render('WallabagCoreBundle:Developer:howto_app.html.twig');
    }
}
