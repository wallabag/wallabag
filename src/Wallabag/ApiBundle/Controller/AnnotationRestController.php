<?php

namespace Wallabag\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\AnnotationBundle\Entity\Annotation;
use Wallabag\CoreBundle\Entity\Entry;

class AnnotationRestController extends WallabagRestController
{
    /**
     * Retrieve annotations for an entry.
     *
     * @Operation(
     *     tags={"Annotations"},
     *     summary="Retrieve annotations for an entry.",
     *     @SWG\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         pattern="\w+",
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getAnnotationsAction(Entry $entry)
    {
        $this->validateAuthentication();

        return $this->forward('Wallabag\AnnotationBundle\Controller\WallabagAnnotationController::getAnnotationsAction', [
            'entry' => $entry,
        ]);
    }

    /**
     * Creates a new annotation.
     *
     * @Operation(
     *     tags={"Annotations"},
     *     summary="Creates a new annotation.",
     *     @SWG\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         pattern="\w+",
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="ranges",
     *         in="body",
     *         description="The range array for the annotation",
     *         required=false,
     *         pattern="\w+",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="quote",
     *         in="body",
     *         description="The annotated text",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Parameter(
     *         name="text",
     *         in="body",
     *         description="Content of annotation",
     *         required=true,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function postAnnotationAction(Request $request, Entry $entry)
    {
        $this->validateAuthentication();

        return $this->forward('Wallabag\AnnotationBundle\Controller\WallabagAnnotationController::postAnnotationAction', [
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
     *     @SWG\Parameter(
     *         name="annotation",
     *         in="path",
     *         description="The annotation ID",
     *         required=true,
     *         pattern="\w+",
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @ParamConverter("annotation", class="Wallabag\AnnotationBundle\Entity\Annotation")
     *
     * @return JsonResponse
     */
    public function putAnnotationAction(Annotation $annotation, Request $request)
    {
        $this->validateAuthentication();

        return $this->forward('Wallabag\AnnotationBundle\Controller\WallabagAnnotationController::putAnnotationAction', [
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
     *     @SWG\Parameter(
     *         name="annotation",
     *         in="path",
     *         description="The annotation ID",
     *         required=true,
     *         pattern="\w+",
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @ParamConverter("annotation", class="Wallabag\AnnotationBundle\Entity\Annotation")
     *
     * @return JsonResponse
     */
    public function deleteAnnotationAction(Annotation $annotation)
    {
        $this->validateAuthentication();

        return $this->forward('Wallabag\AnnotationBundle\Controller\WallabagAnnotationController::deleteAnnotationAction', [
            'annotation' => $annotation,
        ]);
    }
}
