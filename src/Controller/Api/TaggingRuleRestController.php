<?php

namespace Wallabag\Controller\Api;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Nelmio\ApiDocBundle\Annotation\Operation;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaggingRuleRestController extends WallabagRestController
{
    /**
     * Export all tagging rules as a json file.
     *
     * @Operation(
     *     tags={"TaggingRule"},
     *     summary="Export all tagging rules as a json file.",
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @return Response
     */
    #[Route(path: '/api/taggingrule/export.{_format}', name: 'api_get_taggingrule_export', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getTaggingruleExportAction()
    {
        $this->validateAuthentication();

        $data = SerializerBuilder::create()->build()->serialize(
            $this->getUser()->getConfig()->getTaggingRules(),
            'json',
            SerializationContext::create()->setGroups(['export_tagging_rule'])
        );

        return new Response(
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
