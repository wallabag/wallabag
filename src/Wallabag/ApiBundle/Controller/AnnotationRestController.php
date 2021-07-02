<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
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
     * @return JsonResponse
     *
     * @Get(
     *  path="/api/annotations/{entry}.{_format}",
     *  name="api_get_annotations",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     *  )
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
     *
     * @Post(
     *  path="/api/annotations/{entry}.{_format}",
     *  name="api_post_annotation",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
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
     * @return JsonResponse
     *
     * @Put(
     *  path="/api/annotations/{annotation}.{_format}",
     *  name="api_put_annotation",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
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
     * @return JsonResponse
     *
     * @Delete(
     *  path="/api/annotations/{annotation}.{_format}",
     *  name="api_delete_annotation",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
     */
    public function deleteAnnotationAction(Annotation $annotation)
    {
        $this->validateAuthentication();

        return $this->forward('WallabagAnnotationBundle:WallabagAnnotation:deleteAnnotation', [
            'annotation' => $annotation,
        ]);
    }
}
