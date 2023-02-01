<?php

namespace Wallabag\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\AnnotationBundle\Entity\Annotation;
use Wallabag\CoreBundle\Entity\Entry;

class AnnotationRestController extends WallabagRestController
{
    /**
     * Retrieve annotations for an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function getAnnotationsAction(Entry $entry)
    {
        $this->validateAuthentication();

        return $this->forward('WallabagAnnotationBundle:WallabagAnnotation:getAnnotations', [
            'entry' => $entry,
        ]);
    }

    /**
     * Creates a new annotation.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="ranges", "dataType"="array", "requirement"="\w+", "description"="The range array for the annotation"},
     *          {"name"="quote", "dataType"="string", "description"="The annotated text"},
     *          {"name"="text", "dataType"="string", "required"=true, "description"="Content of annotation"},
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function postAnnotationAction(Request $request, Entry $entry)
    {
        $this->validateAuthentication();

        return $this->forward('WallabagAnnotationBundle:WallabagAnnotation:postAnnotation', [
            'request' => $request,
            'entry' => $entry,
        ]);
    }

    /**
     * Updates an annotation.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="annotation", "dataType"="string", "requirement"="\w+", "description"="The annotation ID"}
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function putAnnotationAction(int $annotation, Request $request)
    {
        $this->validateAuthentication();

        return $this->forward('WallabagAnnotationBundle:WallabagAnnotation:putAnnotation', [
            'annotation' => $annotation,
            'request' => $request,
        ]);
    }

    /**
     * Removes an annotation.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="annotation", "dataType"="string", "requirement"="\w+", "description"="The annotation ID"}
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function deleteAnnotationAction(int $annotation)
    {
        $this->validateAuthentication();

        return $this->forward('WallabagAnnotationBundle:WallabagAnnotation:deleteAnnotation', [
            'annotation' => $annotation,
        ]);
    }
}
