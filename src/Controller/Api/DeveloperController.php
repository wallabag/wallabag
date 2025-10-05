<?php

namespace Wallabag\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Controller\AbstractController;
use Wallabag\Entity\Api\Client;
use Wallabag\Form\Type\Api\ClientType;
use Wallabag\Repository\Api\ClientRepository;

class DeveloperController extends AbstractController
{
    /**
     * List all clients and link to create a new one.
     *
     * @return Response
     */
    #[Route(path: '/developer', name: 'developer', methods: ['GET'])]
    public function indexAction(ClientRepository $repo)
    {
        $clients = $repo->findByUser($this->getUser()->getId());

        return $this->render('Developer/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    /**
     * Create a client (an app).
     *
     * @return Response
     */
    #[Route(path: '/developer/client/create', name: 'developer_create_client', methods: ['GET', 'POST'])]
    public function createClientAction(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $client = new Client($this->getUser());
        $clientForm = $this->createForm(ClientType::class, $client);
        $clientForm->handleRequest($request);

        if ($clientForm->isSubmitted() && $clientForm->isValid()) {
            $client->setAllowedGrantTypes(['token', 'authorization_code', 'password', 'refresh_token']);
            $entityManager->persist($client);
            $entityManager->flush();

            $this->addFlash(
                'notice',
                $translator->trans('flashes.developer.notice.client_created', ['%name%' => $client->getName()])
            );

            return $this->render('Developer/client_parameters.html.twig', [
                'client_id' => $client->getPublicId(),
                'client_secret' => $client->getSecret(),
                'client_name' => $client->getName(),
            ]);
        }

        return $this->render('Developer/client.html.twig', [
            'form' => $clientForm->createView(),
        ]);
    }

    /**
     * Remove a client.
     *
     * @return RedirectResponse
     */
    #[Route(path: '/developer/client/delete/{id}', name: 'developer_delete_client', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteClientAction(Request $request, Client $client, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        if (!$this->isCsrfTokenValid('delete-client', $request->request->get('token'))) {
            throw new BadRequestHttpException('Bad CSRF token.');
        }

        if (null === $this->getUser() || $client->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this client.');
        }

        $entityManager->remove($client);
        $entityManager->flush();

        $this->addFlash(
            'notice',
            $translator->trans('flashes.developer.notice.client_deleted', ['%name%' => $client->getName()])
        );

        return $this->redirect($this->generateUrl('developer'));
    }

    /**
     * Display developer how to use an existing app.
     *
     * @return Response
     */
    #[Route(path: '/developer/howto/first-app', name: 'developer_howto_firstapp', methods: ['GET'])]
    public function howtoFirstAppAction()
    {
        return $this->render('Developer/howto_app.html.twig');
    }
}
