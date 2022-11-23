<?php

namespace Wallabag\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Wallabag\ApiBundle\Entity\Client;
use Wallabag\ApiBundle\Form\Type\ClientType;

class DeveloperController extends Controller
{
    /**
     * List all clients and link to create a new one.
     *
     * @Route("/developer", name="developer")
     *
     * @return Response
     */
    public function indexAction()
    {
        $clients = $this->getDoctrine()->getRepository(Client::class)->findByUser($this->getUser()->getId());

        return $this->render('@WallabagCore/Developer/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    /**
     * Create a client (an app).
     *
     * @Route("/developer/client/create", name="developer_create_client")
     *
     * @return Response
     */
    public function createClientAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $client = new Client($this->getUser());
        $clientForm = $this->createForm(ClientType::class, $client);
        $clientForm->handleRequest($request);

        if ($clientForm->isSubmitted() && $clientForm->isValid()) {
            $client->setAllowedGrantTypes(['token', 'authorization_code', 'password', 'refresh_token']);
            $em->persist($client);
            $em->flush();

            $this->get(SessionInterface::class)->getFlashBag()->add(
                'notice',
                $this->get(TranslatorInterface::class)->trans('flashes.developer.notice.client_created', ['%name%' => $client->getName()])
            );

            return $this->render('@WallabagCore/Developer/client_parameters.html.twig', [
                'client_id' => $client->getPublicId(),
                'client_secret' => $client->getSecret(),
                'client_name' => $client->getName(),
            ]);
        }

        return $this->render('@WallabagCore/Developer/client.html.twig', [
            'form' => $clientForm->createView(),
        ]);
    }

    /**
     * Remove a client.
     *
     * @Route("/developer/client/delete/{id}", requirements={"id" = "\d+"}, name="developer_delete_client")
     *
     * @return RedirectResponse
     */
    public function deleteClientAction(Client $client)
    {
        if (null === $this->getUser() || $client->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this client.');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($client);
        $em->flush();

        $this->get(SessionInterface::class)->getFlashBag()->add(
            'notice',
            $this->get(TranslatorInterface::class)->trans('flashes.developer.notice.client_deleted', ['%name%' => $client->getName()])
        );

        return $this->redirect($this->generateUrl('developer'));
    }

    /**
     * Display developer how to use an existing app.
     *
     * @Route("/developer/howto/first-app", name="developer_howto_firstapp")
     *
     * @return Response
     */
    public function howtoFirstAppAction()
    {
        return $this->render('@WallabagCore/Developer/howto_app.html.twig');
    }
}
