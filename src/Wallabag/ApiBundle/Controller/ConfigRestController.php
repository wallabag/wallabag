<?php

namespace Wallabag\ApiBundle\Controller;

use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConfigRestController extends WallabagRestController
{
    /**
     * Retrieve configuration for current user.
     *
     * @ApiDoc()
     *
     * @return JsonResponse
     */
    public function getConfigAction()
    {
        $this->validateAuthentication();

        $json = $this->get('jms_serializer')->serialize(
            $this->getUser()->getConfig(),
            'json',
            SerializationContext::create()->setGroups(['config_api'])
        );

        return (new JsonResponse())
            ->setJson($json)
            ->setStatusCode(JsonResponse::HTTP_OK);
    }
}
