<?php

namespace Wallabag\AnnotationBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Wallabag\AnnotationBundle\Entity\Annotation;
use Wallabag\CoreBundle\Entity\Entry;

class WallabagAnnotationController extends FOSRestController
{
    /**
     * Retrieve annotations for an entry.
     *
     * @param Entry $entry
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @return JsonResponse
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

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Creates a new annotation.
     *
     * @param Request $request
     * @param Entry   $entry
     *
     * @return JsonResponse
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
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

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Updates an annotation.
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
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
        $data = json_decode($request->getContent(), true);

        if (!is_null($data['text'])) {
            $annotation->setText($data['text']);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $json = $this->get('serializer')->serialize($annotation, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Removes an annotation.
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @ParamConverter("annotation", class="WallabagAnnotationBundle:Annotation")
     *
     * @param Annotation $annotation
     *
     * @return JsonResponse
     */
    public function deleteAnnotationAction(Annotation $annotation)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($annotation);
        $em->flush();

        $json = $this->get('serializer')->serialize($annotation, 'json');

        return (new JsonResponse())->setJson($json);
    }
}
