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
        $clients = $this->getDoctrine()->getRepository('WallabagApiBundle:Client')->findAll();

        return $this->render('WallabagCoreBundle:Developer:index.html.twig', array(
            'clients' => $clients,
        ));
    }

    /**
     * @param Request $request
     *
     * @Route("/developer/client/create", name="developer_create_client")
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
            $client->setAllowedGrantTypes(array('token', 'authorization_code', 'password'));
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
     * Remove a client.
     *
     * @param Request $request
     *
     * @Route("/developer/client/delete/{id}", requirements={"id" = "\d+"}, name="developer_delete_client")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteClientAction(Request $request, Client $client)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($client);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Client deleted'
        );

        return $this->redirect($this->generateUrl('developer'));
    }

    /**
     * @Route("/developer/howto/first-app", name="developer_howto_firstapp")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function howtoFirstAppAction()
    {
        return $this->render('WallabagCoreBundle:Developer:howto_app.html.twig');
    }
}
