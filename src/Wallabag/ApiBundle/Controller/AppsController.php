<?php

namespace Wallabag\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\ApiBundle\Entity\AccessToken;
use Wallabag\ApiBundle\Entity\Client;
use Wallabag\ApiBundle\Form\Type\ClientType;

class AppsController extends Controller
{
    /**
     * List all clients and link to create a new one.
     *
     * @Route("/apps", name="apps")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $clients = $this->getDoctrine()->getRepository('WallabagApiBundle:Client')->findByUser($this->getUser()->getId());

        $apps = $this->getDoctrine()->getRepository('WallabagApiBundle:AccessToken')->findAppsByUser($this->getUser()->getId());

        return $this->render('@WallabagCore/themes/common/Developer/index.html.twig', [
            'clients' => $clients,
            'apps' => $apps,
        ]);
    }

    /**
     * Create a an app
     *
     * @param Request $request
     *
     * @Route("/api/apps", name="apps_create")
     * @Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAppAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $clientName = $request->request->get('client_name');
        $redirectURIs = $request->request->get('redirect_uris');
        $logoURI = $request->request->get('logo_uri');
        $description = $request->request->get('description');
        $appURI = $request->request->get('app_uri');
        $nextRedirect = $request->request->get('uri_redirect_after_creation');

        if (!$clientName) {
            return new JsonResponse([
                'error' => 'invalid_client_name',
                'error_description' => 'The client name cannot be empty',
            ], 400);
        }

        if (!$redirectURIs) {
            return new JsonResponse([
                'error' => 'invalid_redirect_uri',
                'error_description' => 'One or more redirect_uri values are invalid',
            ], 400);
        }

        $redirectURIs = (array) $redirectURIs;

        $client = new Client();

        $client->setName($clientName);

        $client->setDescription($description);

        $client->setRedirectUris($redirectURIs);

        $client->setImage($logoURI);
        $client->setAppUrl($appURI);

        $client->setAllowedGrantTypes(['token', 'refresh_token', 'authorization_code']);
        $em->persist($client);
        $em->flush();

        return new JsonResponse([
            'client_id' => $client->getPublicId(),
            'client_secret' => $client->getSecret(),
            'client_name' => $client->getName(),
            'redirect_uri' => $client->getRedirectUris(),
            'description' => $client->getDescription(),
            'logo_uri' => $client->getImage(),
            'app_uri' => $client->getAppUrl(),
        ], 201);
    }

    /**
     * Create a client (an app).
     *
     * @param Request $request
     *
     * @Route("/apps/client/create", name="apps_create_client")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createClientAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $client = new Client($this->getUser());
        $clientForm = $this->createForm(ClientType::class, $client);
        $clientForm->handleRequest($request);

        if ($clientForm->isSubmitted() && $clientForm->isValid()) {
            $client->setAllowedGrantTypes(['password', 'token', 'refresh_token', 'client_credentials']); // Password is depreciated
            $em->persist($client);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.developer.notice.client_created', ['%name%' => $client->getName()])
            );

            return $this->render('@WallabagCore/themes/common/Developer/client_parameters.html.twig', [
                'client_id' => $client->getPublicId(),
                'client_secret' => $client->getSecret(),
                'client_name' => $client->getName(),
            ]);
        }

        return $this->render('@WallabagCore/themes/common/Developer/client.html.twig', [
            'form' => $clientForm->createView(),
        ]);
    }

    /**
     * Revoke an access token
     * @param $token
     * @Route("/api/revoke/{token}", name="apps_revoke_access_token")
     * @return JsonResponse
     */
    public function removeAccessTokenAction($token)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $accessToken = $em->getRepository('WallabagApiBundle:AccessToken')->findOneBy([
            'user' => $this->getUser()->getId(),
            'token' => $token
            ]);
        if ($accessToken) {
            $em->remove($accessToken);
            $em->flush();

            return new JsonResponse([], 204);
        }
        return new JsonResponse([], 404);
    }

    /**
     * Remove a client.
     *
     * @param Client $client
     *
     * @Route("/apps/client/delete/{id}", requirements={"id" = "\d+"}, name="apps_delete_client")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteClientAction(Client $client)
    {
        if (null === $this->getUser() || $client->getUser()->getId() != $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this client.');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($client);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            $this->get('translator')->trans('flashes.developer.notice.client_deleted', ['%name%' => $client->getName()])
        );

        return $this->redirect($this->generateUrl('apps'));
    }
}
