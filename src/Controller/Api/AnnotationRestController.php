<?php

namespace Wallabag\Controller\Api;

use Nelmio\ApiDocBundle\Annotation\Operation;
use OpenApi\Annotations as OA;
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
     * @Route("/api/annotations/{entry}.{_format}", methods={"GET"}, name="api_get_annotations", defaults={"_format": "json"})
     *
     * @return Response
     */
    public function getAnnotationsAction(Entry $entry)
    {
        $this->validateAuthentication();

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
     * @Route("/api/annotations/{entry}.{_format}", methods={"POST"}, name="api_post_annotation", defaults={"_format": "json"})
     *
     * @return Response
     */
    public function postAnnotationAction(Request $request, Entry $entry)
    {
        $this->validateAuthentication();

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
     * @Route("/api/annotations/{annotation}.{_format}", methods={"PUT"}, name="api_put_annotation", defaults={"_format": "json"})
     *
     * @return Response
     */
    public function putAnnotationAction(int $annotation, Request $request)
    {
        $this->validateAuthentication();

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
     * @Route("/api/annotations/{annotation}.{_format}", methods={"DELETE"}, name="api_delete_annotation", defaults={"_format": "json"})
     *
     * @return Response
     */
    public function deleteAnnotationAction(int $annotation)
    {
        $this->validateAuthentication();

        return $this->forward('Wallabag\Controller\AnnotationController::deleteAnnotationAction', [
            'annotation' => $annotation,
        ]);
    }
}
