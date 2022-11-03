<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WallabagRestController extends AbstractFOSRestController
{
    /**
     * Retrieve version number.
     *
     * @ApiDoc()
     *
     * @deprecated Should use info endpoint instead
     *
     * @return JsonResponse
     */
    public function getVersionAction()
    {
        $version = $this->container->getParameter('wallabag_core.version');
        $json = $this->get(SerializerInterface::class)->serialize($version, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Retrieve information about the wallabag instance.
     *
     * @ApiDoc()
     *
     * @return JsonResponse
     */
    public function getInfoAction()
    {
        $info = [
            'appname' => 'wallabag',
            'version' => $this->container->getParameter('wallabag_core.version'),
            'allowed_registration' => $this->container->getParameter('fosuser_registration'),
        ];

        return (new JsonResponse())->setJson($this->get(SerializerInterface::class)->serialize($info, 'json'));
    }

    protected function validateAuthentication()
    {
        if (false === $this->get(AuthorizationCheckerInterface::class)->isGranted('IS_AUTHENTICATED_FULLY')) {
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
        $user = $this->get(TokenStorageInterface::class)->getToken()->getUser();
        if ($requestUserId !== $user->getId()) {
            throw $this->createAccessDeniedException('Access forbidden. Entry user id: ' . $requestUserId . ', logged user id: ' . $user->getId());
        }
    }

    /**
     * Shortcut to send data serialized in json.
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    protected function sendResponse($data)
    {
        // https://github.com/schmittjoh/JMSSerializerBundle/issues/293
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $json = $this->get(SerializerInterface::class)->serialize($data, 'json', $context);

        return (new JsonResponse())->setJson($json);
    }
}
