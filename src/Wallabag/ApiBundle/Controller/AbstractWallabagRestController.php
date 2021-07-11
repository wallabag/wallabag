<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Craue\ConfigBundle\Util\Config;
use FOS\UserBundle\Model\UserManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractWallabagRestController extends AbstractFOSRestController
{
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'craue_config' => Config::class,
                'event_dispatcher' => EventDispatcherInterface::class, // Should move to DI
                'fos_user.user_manager' => UserManagerInterface::class,
                'jms_serializer' => SerializerInterface::class,
                'translator' => TranslatorInterface::class,
            ]
        );
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
