<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * Register an user.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="username", "dataType"="string", "required"=true, "description"="The user's username"},
     *          {"name"="password", "dataType"="string", "required"=true, "description"="The user's password"},
     *          {"name"="email", "dataType"="string", "required"=true, "description"="The user's email"}
     *      }
     * )
     *
     * @todo Make this method (or the whole API) accessible only through https
     *
     * @return JsonResponse
     */
    public function putUserAction(Request $request)
    {
        if (!$this->getParameter('fosuser_registration') || !$this->get('craue_config')->get('api_user_registration')) {
            $json = $this->get('serializer')->serialize(['error' => "Server doesn't allow registrations"], 'json');

            return (new JsonResponse())->setJson($json)->setStatusCode(403);
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
            $data = json_decode($this->handleView($view)->getContent(), true)['children'];
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

            $json = $this->get('serializer')->serialize(['error' => $errors], 'json');

            return (new JsonResponse())->setJson($json)->setStatusCode(400);
        }

        $userManager->updateUser($user);

        // dispatch a created event so the associated config will be created
        $event = new UserEvent($user, $request);
        $this->get('event_dispatcher')->dispatch(FOSUserEvents::USER_CREATED, $event);

        return $this->sendUser($user);
    }

    /**
     * Send user response.
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    private function sendUser(User $user)
    {
        $json = $this->get('serializer')->serialize(
            $user,
            'json',
            SerializationContext::create()->setGroups(['user_api'])
        );

        return (new JsonResponse())->setJson($json);
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
