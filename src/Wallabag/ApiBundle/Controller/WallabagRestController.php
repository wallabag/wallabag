<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wallabag\UserBundle\Entity\User;

class WallabagRestController extends AbstractFOSRestController
{
    /**
     * Retrieve version number.
     *
     * @Operation(
     *     tags={"Informations"},
     *     summary="Retrieve version number.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @deprecated Should use info endpoint instead
     *
     * @Route("/api/version.{_format}", methods={"GET"}, name="api_get_version", defaults={"_format": "json"})
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
     * @Operation(
     *     tags={"Informations"},
     *     summary="Retrieve information about the wallabag instance.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/info.{_format}", methods={"GET"}, name="api_get_info", defaults={"_format": "json"})
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
        \assert($user instanceof User);
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
