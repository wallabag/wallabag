<?php

namespace Wallabag\Controller\Api;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Operation;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConfigRestController extends WallabagRestController
{
    /**
     * Retrieve configuration for current user.
     *
     * @Operation(
     *     tags={"Config"},
     *     summary="Retrieve configuration for current user.",
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @return JsonResponse
     */
    #[Route(path: '/api/config.{_format}', name: 'api_get_config', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getConfigAction(SerializerInterface $serializer)
    {
        $this->validateAuthentication();

        $json = $serializer->serialize(
            $this->getUser()->getConfig(),
            'json',
            SerializationContext::create()->setGroups(['config_api'])
        );

        return (new JsonResponse())
            ->setJson($json)
            ->setStatusCode(JsonResponse::HTTP_OK);
    }

    /** @Route("/api/config.{_format}", methods={"PATCH"}, name="api_patch_content_config", defaults={"_format": "json"}) */
    public function patchContentConfigAction(Request $request): JsonResponse
    {
        $this->validateAuthentication();
        $data = \strlen($request->getContent()) <= 1024 ? json_decode($request->getContent(), true) : null;
        if (!\is_array($data) || !\is_bool($data['x_article_reader_enabled'] ?? null)) {
            return new JsonResponse(['error' => 'Expected boolean x_article_reader_enabled. Enabling shares public X post IDs and server IP with FxTwitter.'], 400);
        }
        $config = $this->getUser()->getConfig();
        $config->setXArticleReaderEnabled($data['x_article_reader_enabled']);
        $this->entityManager->flush();

        return new JsonResponse(['x_article_reader_enabled' => $config->isXArticleReaderEnabled()]);
    }
}
