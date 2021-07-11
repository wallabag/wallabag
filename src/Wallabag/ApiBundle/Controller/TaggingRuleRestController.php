<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

class TaggingRuleRestController extends AbstractWallabagRestController
{
    /**
     * Export all tagging rules as a json file.
     *
     * @ApiDoc()
     *
     * @return Response
     *
     * @Get(
     *  path="/api/taggingrule/export.{_format}",
     *  name="api_get_taggingrule_export",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
     */
    public function getTaggingruleExportAction()
    {
        $this->validateAuthentication();

        $data = SerializerBuilder::create()->build()->serialize(
            $this->getUser()->getConfig()->getTaggingRules(),
            'json',
            SerializationContext::create()->setGroups(['export_tagging_rule'])
        );

        return Response::create(
            $data,
            200,
            [
                'Content-type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="tagging_rules_' . $this->getUser()->getUsername() . '.json"',
                'Content-Transfer-Encoding' => 'UTF-8',
            ]
        );
    }
}
