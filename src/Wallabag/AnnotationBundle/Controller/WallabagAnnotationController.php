<?php

namespace Wallabag\AnnotationBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Wallabag\AnnotationBundle\Entity\Annotation;
use Wallabag\CoreBundle\Entity\Entry;

class WallabagAnnotationController extends FOSRestController
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
     * @return Response
     */
    public function getAnnotationsAction(Entry $entry)
    {
        $annotationRows = $this
                ->getDoctrine()
                ->getRepository('WallabagAnnotationBundle:Annotation')
                ->findAnnotationsByPageId($entry->getId(), $this->getUser()->getId());
        $total = count($annotationRows);
        $annotations = array('total' => $total, 'rows' => $annotationRows);

        $json = $this->get('serializer')->serialize($annotations, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Creates a new annotation.
     *
     * @param Entry $entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="ranges", "dataType"="array", "requirement"="\w+", "description"="The range array for the annotation"},
     *          {"name"="quote", "dataType"="string", "required"=false, "description"="Optional, quote for the annotation"},
     *          {"name"="text", "dataType"="string", "required"=true, "description"=""},
     *      }
     * )
     *
     * @return Response
     */
    public function postAnnotationAction(Request $request, Entry $entry)
    {
        $data = json_decode($request->getContent(), true);

        $em = $this->getDoctrine()->getManager();

        $annotation = new Annotation($this->getUser());

        $annotation->setText($data['text']);
        if (array_key_exists('quote', $data)) {
            $annotation->setQuote($data['quote']);
        }
        if (array_key_exists('ranges', $data)) {
            $annotation->setRanges($data['ranges']);
        }

        $annotation->setEntry($entry);

        $em->persist($annotation);
        $em->flush();

        $json = $this->get('serializer')->serialize($annotation, 'json');

        return $this->renderJsonResponse($json);
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
     * @return Response
     */
    public function putAnnotationAction(Annotation $annotation, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!is_null($data['text'])) {
            $annotation->setText($data['text']);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $json = $this->get('serializer')->serialize($annotation, 'json');

        return $this->renderJsonResponse($json);
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
     * @return Response
     */
    public function deleteAnnotationAction(Annotation $annotation)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($annotation);
        $em->flush();

        $json = $this->get('serializer')->serialize($annotation, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Send a JSON Response.
     * We don't use the Symfony JsonRespone, because it takes an array as parameter instead of a JSON string.
     *
     * @param string $json
     *
     * @return Response
     */
    private function renderJsonResponse($json, $code = 200)
    {
        return new Response($json, $code, array('application/json'));
    }
}
