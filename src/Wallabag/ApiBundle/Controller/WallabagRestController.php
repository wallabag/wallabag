<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WallabagRestController extends FOSRestController
{
    /**
     * Retrieve version number.
     *
     * @ApiDoc()
     *
     * @return JsonResponse
     */
    public function getVersionAction()
    {
        $version = $this->container->getParameter('wallabag_core.version');
        $json = $this->get('jms_serializer')->serialize($version, 'json');

        return (new JsonResponse())->setJson($json);
    }

    protected function validateAuthentication()
    {
        if (false === $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Validate that the first id is equal to the second one.
     * If not, throw exception. It means a user try to access information from an other user.
     *
     * @param int $requestUserId User id from the requested source
     */
    protected function validateUserAccess($requestUserId)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($requestUserId !== $user->getId()) {
            throw $this->createAccessDeniedException('Access forbidden. Entry user id: ' . $requestUserId . ', logged user id: ' . $user->getId());
        }
    }
}
