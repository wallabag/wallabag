<?php

namespace Wallabag\Controller\Api;

use Nelmio\ApiDocBundle\Annotation\Operation;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Entity\Annotation;
use Wallabag\Entity\Entry;

class AnnotationRestController extends WallabagRestController
{
    /**
     * Retrieve annotations for an entry.
     *
     * @Operation(
     *     tags={"Annotations"},
     *     summary="Retrieve annotations for an entry.",
     *     @OA\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @return Response
     */
    #[Route(path: '/api/annotations/{entry}.{_format}', name: 'api_get_annotations', methods: ['GET'], defaults: ['_format' => 'json'])]
    #[IsGranted('LIST_ANNOTATIONS', subject: 'entry')]
    public function getAnnotationsAction(Entry $entry)
    {
        return $this->forward('Wallabag\Controller\AnnotationController::getAnnotationsAction', [
            'entry' => $entry,
        ]);
    }

    /**
     * Creates a new annotation.
     *
     * @Operation(
     *     tags={"Annotations"},
     *     summary="Creates a new annotation.",
     *     @OA\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={"text"},
     *              @OA\Property(
     *                  property="ranges",
     *                  type="array",
     *                  description="The range array for the annotation",
     *                  @OA\Items(
     *                      type="string",
     *                      pattern="\w+",
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="quote",
     *                  type="array",
     *                  description="The annotated text",
     *                  @OA\Items(
     *                      type="string",
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="text",
     *                  type="array",
     *                  description="Content of annotation",
     *                  @OA\Items(
     *                      type="string",
     *                  )
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @return Response
     */
    #[Route(path: '/api/annotations/{entry}.{_format}', name: 'api_post_annotation', methods: ['POST'], defaults: ['_format' => 'json'])]
    #[IsGranted('CREATE_ANNOTATIONS', subject: 'entry')]
    public function postAnnotationAction(Request $request, Entry $entry)
    {
        return $this->forward('Wallabag\Controller\AnnotationController::postAnnotationAction', [
            'request' => $request,
            'entry' => $entry,
        ]);
    }

    /**
     * Updates an annotation.
     *
     * @Operation(
     *     tags={"Annotations"},
     *     summary="Updates an annotation.",
     *     @OA\Parameter(
     *         name="annotation",
     *         in="path",
     *         description="The annotation ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @return Response
     */
    #[Route(path: '/api/annotations/{annotation}.{_format}', name: 'api_put_annotation', methods: ['PUT'], defaults: ['_format' => 'json'])]
    #[IsGranted('EDIT', subject: 'annotation')]
    public function putAnnotationAction(Annotation $annotation, Request $request)
    {
        return $this->forward('Wallabag\Controller\AnnotationController::putAnnotationAction', [
            'annotation' => $annotation,
            'request' => $request,
        ]);
    }

    /**
     * Removes an annotation.
     *
     * @Operation(
     *     tags={"Annotations"},
     *     summary="Removes an annotation.",
     *     @OA\Parameter(
     *         name="annotation",
     *         in="path",
     *         description="The annotation ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @return Response
     */
    #[Route(path: '/api/annotations/{annotation}.{_format}', name: 'api_delete_annotation', methods: ['DELETE'], defaults: ['_format' => 'json'])]
    #[IsGranted('DELETE', subject: 'annotation')]
    public function deleteAnnotationAction(Annotation $annotation)
    {
        return $this->forward('Wallabag\Controller\AnnotationController::deleteAnnotationAction', [
            'annotation' => $annotation,
        ]);
    }
}
