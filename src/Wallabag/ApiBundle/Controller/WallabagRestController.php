<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;

class WallabagRestController extends AbstractWallabagRestController
{
    private $version;
    private $registrationEnabled;

    public function __construct(string $version, bool $registrationEnabled)
    {
        $this->version = $version;
        $this->registrationEnabled = $registrationEnabled;
    }

    /**
     * Retrieve version number.
     *
     * @ApiDoc()
     *
     * @deprecated Should use info endpoint instead
     *
     * @return JsonResponse
     *
     * @Get(
     *  path="/api/version.{_format}",
     *  name="api_get_version",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
     */
    public function getVersionAction()
    {
        $json = $this->get('jms_serializer')->serialize($this->version, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Retrieve information about the wallabag instance.
     *
     * @ApiDoc()
     *
     * @return JsonResponse
     *
     * @Get(
     *  path="/api/info.{_format}",
     *  name="api_get_info",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
     */
    public function getInfoAction()
    {
        $info = [
            'appname' => 'wallabag',
            'version' => $this->version,
            'allowed_registration' => $this->registrationEnabled,
        ];

        return (new JsonResponse())->setJson($this->get('jms_serializer')->serialize($info, 'json'));
    }
}
