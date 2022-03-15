<?php

namespace Wallabag\ApiBundle\Controller;

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

        return $this->sendResponse($this->getUser()->getConfig());
    }
}
