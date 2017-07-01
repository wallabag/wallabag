<?php

namespace Wallabag\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @param Entry $entry
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
     *          {"name"="quote", "dataType"="string", "required"=false, "description"="Optional, quote for the annotation"},
     *          {"name"="text", "dataType"="string", "required"=true, "description"=""},
     *      }
     * )
     *
     * @param Request $request
     * @param Entry   $entry
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
     * @ParamConverter("annotation", class="WallabagAnnotationBundle:Annotation")
     *
     * @param Annotation $annotation
     * @param Request    $request
     *
     * @return JsonResponse
     */
    public function putAnnotationAction(Annotation $annotation, Request $request)
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
     * @ParamConverter("annotation", class="WallabagAnnotationBundle:Annotation")
     *
     * @param Annotation $annotation
     *
     * @return JsonResponse
     */
    public function deleteAnnotationAction(Annotation $annotation)
    {
        $this->validateAuthentication();

        return $this->forward('WallabagAnnotationBundle:WallabagAnnotation:deleteAnnotation', [
            'annotation' => $annotation,
        ]);
    }
}
