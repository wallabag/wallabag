<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\ApiBundle\Entity\Client;
use Wallabag\UserBundle\Entity\User;

class UserRestController extends WallabagRestController
{
    /**
     * Retrieve current logged in user informations.
     *
     * @ApiDoc()
     *
     * @return JsonResponse
     */
    public function getUserAction()
    {
        $this->validateAuthentication();

        return $this->sendUser($this->getUser());
    }

    /**
     * Register an user and create a client.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="username", "dataType"="string", "required"=true, "description"="The user's username"},
     *          {"name"="password", "dataType"="string", "required"=true, "description"="The user's password"},
     *          {"name"="email", "dataType"="string", "required"=true, "description"="The user's email"},
     *          {"name"="client_name", "dataType"="string", "required"=true, "description"="The client name (to be used by your app)"}
     *      }
     * )
     *
     * @todo Make this method (or the whole API) accessible only through https
     *
     * @return JsonResponse
     */
    public function putUserAction(Request $request)
    {
        if (!$this->container->getParameter('fosuser_registration') || !$this->get('craue_config')->get('api_user_registration')) {
            $json = $this->get('jms_serializer')->serialize(['error' => "Server doesn't allow registrations"], 'json');

            return (new JsonResponse())
                ->setJson($json)
                ->setStatusCode(JsonResponse::HTTP_FORBIDDEN);
        }

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        // user will be disabled BY DEFAULT to avoid spamming account to be enabled
        $user->setEnabled(false);

        $form = $this->createForm('Wallabag\UserBundle\Form\NewUserType', $user, [
            'csrf_protection' => false,
        ]);

        // simulate form submission
        $form->submit([
            'username' => $request->request->get('username'),
            'plainPassword' => [
                'first' => $request->request->get('password'),
                'second' => $request->request->get('password'),
            ],
            'email' => $request->request->get('email'),
        ]);

        if ($form->isSubmitted() && false === $form->isValid()) {
            $view = $this->view($form, 400);
            $view->setFormat('json');

            // handle errors in a more beautiful way than the default view
            $data = json_decode($this->handleView($view)->getContent(), true)['errors']['children'];
            $errors = [];

            if (isset($data['username']['errors'])) {
                $errors['username'] = $this->translateErrors($data['username']['errors']);
            }

            if (isset($data['email']['errors'])) {
                $errors['email'] = $this->translateErrors($data['email']['errors']);
            }

            if (isset($data['plainPassword']['children']['first']['errors'])) {
                $errors['password'] = $this->translateErrors($data['plainPassword']['children']['first']['errors']);
            }

            $json = $this->get('jms_serializer')->serialize(['error' => $errors], 'json');

            return (new JsonResponse())
                ->setJson($json)
                ->setStatusCode(JsonResponse::HTTP_BAD_REQUEST);
        }

        // create a default client
        $client = new Client($user);
        $client->setName($request->request->get('client_name', 'Default client'));

        $this->getDoctrine()->getManager()->persist($client);

        $user->addClient($client);

        $userManager->updateUser($user);

        // dispatch a created event so the associated config will be created
        $event = new UserEvent($user, $request);
        $this->get('event_dispatcher')->dispatch(FOSUserEvents::USER_CREATED, $event);

        return $this->sendUser($user, 'user_api_with_client', JsonResponse::HTTP_CREATED);
    }

    /**
     * Send user response.
     *
     * @param string $group  Used to define with serialized group might be used
     * @param int    $status HTTP Status code to send
     *
     * @return JsonResponse
     */
    private function sendUser(User $user, $group = 'user_api', $status = JsonResponse::HTTP_OK)
    {
        $json = $this->get('jms_serializer')->serialize(
            $user,
            'json',
            SerializationContext::create()->setGroups([$group])
        );

        return (new JsonResponse())
            ->setJson($json)
            ->setStatusCode($status);
    }

    /**
     * Translate errors message.
     *
     * @param array $errors
     *
     * @return array
     */
    private function translateErrors($errors)
    {
        $translatedErrors = [];
        foreach ($errors as $error) {
            $translatedErrors[] = $this->get('translator')->trans($error);
        }

        return $translatedErrors;
    }
}
