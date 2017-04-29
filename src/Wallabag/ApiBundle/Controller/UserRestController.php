<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserRestController extends WallabagRestController
{
    /**
     * Retrieve user informations
     *
     * @ApiDoc()
     *
     * @return JsonResponse
     */
    public function getUserAction()
    {
        $this->validateAuthentication();

        $serializationContext = SerializationContext::create()->setGroups(['user_api']);
        $json = $this->get('serializer')->serialize($this->getUser(), 'json', $serializationContext);

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Register an user
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="username", "dataType"="string", "required"=true, "description"="The user's username"},
     *          {"name"="password", "dataType"="string", "required"=true, "description"="The user's password"}
     *          {"name"="email", "dataType"="string", "required"=true, "description"="The user's email"}
     *      }
     * )
     * @return JsonResponse
     */
    // TODO : Make this method (or the whole API) accessible only through https
    public function putUserAction($username, $password, $email)
    {
        if (!$this->container->getParameter('fosuser_registration')) {
            $json = $this->get('serializer')->serialize(['error' => "Server doesn't allow registrations"], 'json');
            return (new JsonResponse())->setJson($json)->setStatusCode(403);
        }

        if ($password === '') { // TODO : might be a good idea to enforce restrictions here
            $json = $this->get('serializer')->serialize(['error' => 'Password is blank'], 'json');
            return (new JsonResponse())->setJson($json)->setStatusCode(400);
        }


        // TODO : Make only one call to database by using a custom repository method
        if ($this->getDoctrine()
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUserName($username)) {
            $json = $this->get('serializer')->serialize(['error' => 'Username is already taken'], 'json');
            return (new JsonResponse())->setJson($json)->setStatusCode(409);
        }

        if ($this->getDoctrine()
            ->getRepository('WallabagUserBundle:User')
            ->findOneByEmail($email)) {
            $json = $this->get('serializer')->serialize(['error' => 'An account with this email already exists'], 'json');
            return (new JsonResponse())->setJson($json)->setStatusCode(409);
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $user->setUsername($username);

        $user->setPlainPassword($password);

        $user->setEmail($email);

        $user->setEnabled(true);
        $user->addRole('ROLE_USER');

        $em->persist($user);

        // dispatch a created event so the associated config will be created
        $event = new UserEvent($user);
        $this->get('event_dispatcher')->dispatch(FOSUserEvents::USER_CREATED, $event);

        $serializationContext = SerializationContext::create()->setGroups(['user_api']);
        $json = $this->get('serializer')->serialize($user, 'json', $serializationContext);

        return (new JsonResponse())->setJson($json);

    }

}
