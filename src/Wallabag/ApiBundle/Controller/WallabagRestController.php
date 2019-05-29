<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $json = $this->get('jms_serializer')->serialize($version, 'json');

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
            'allowed_registration' => $this->container->getParameter('wallabag_user.registration_enabled'),
        ];

        return (new JsonResponse())->setJson($this->get('jms_serializer')->serialize($info, 'json'));
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

        $json = $this->get('jms_serializer')->serialize($data, 'json', $context);

        return (new JsonResponse())->setJson($json);
    }
}
