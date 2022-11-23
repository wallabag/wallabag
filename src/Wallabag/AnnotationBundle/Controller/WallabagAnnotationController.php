<?php

namespace Wallabag\AnnotationBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\AnnotationBundle\Entity\Annotation;
use Wallabag\AnnotationBundle\Form\EditAnnotationType;
use Wallabag\AnnotationBundle\Form\NewAnnotationType;
use Wallabag\CoreBundle\Entity\Entry;

class WallabagAnnotationController extends AbstractFOSRestController
{
    /**
     * Retrieve annotations for an entry.
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @Route("/annotations/{entry}.{_format}", methods={"GET"}, name="annotations_get_annotations", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function getAnnotationsAction(Entry $entry)
    {
        $annotationRows = $this
            ->getDoctrine()
            ->getRepository(Annotation::class)
            ->findAnnotationsByPageId($entry->getId(), $this->getUser()->getId());
        $total = \count($annotationRows);
        $annotations = ['total' => $total, 'rows' => $annotationRows];

        $json = $this->get(SerializerInterface::class)->serialize($annotations, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Creates a new annotation.
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @Route("/annotations/{entry}.{_format}", methods={"POST"}, name="annotations_post_annotation", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function postAnnotationAction(Request $request, Entry $entry)
    {
        $data = json_decode($request->getContent(), true);

        $em = $this->get('doctrine')->getManager();
        $annotation = new Annotation($this->getUser());
        $annotation->setEntry($entry);

        $form = $this->get(FormFactoryInterface::class)->createNamed('', NewAnnotationType::class, $annotation, [
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
        $form->submit($data);

        if ($form->isValid()) {
            $em->persist($annotation);
            $em->flush();

            $json = $this->get(SerializerInterface::class)->serialize($annotation, 'json');

            return JsonResponse::fromJsonString($json);
        }

        return $form;
    }

    /**
     * Updates an annotation.
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @Route("/annotations/{annotation}.{_format}", methods={"PUT"}, name="annotations_put_annotation", defaults={"_format": "json"})
     * @ParamConverter("annotation", class="Wallabag\AnnotationBundle\Entity\Annotation")
     *
     * @return JsonResponse
     */
    public function putAnnotationAction(Annotation $annotation, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $form = $this->get(FormFactoryInterface::class)->createNamed('', EditAnnotationType::class, $annotation, [
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
        $form->submit($data);

        if ($form->isValid()) {
            $em = $this->get('doctrine')->getManager();
            $em->persist($annotation);
            $em->flush();

            $json = $this->get(SerializerInterface::class)->serialize($annotation, 'json');

            return JsonResponse::fromJsonString($json);
        }

        return $form;
    }

    /**
     * Removes an annotation.
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @Route("/annotations/{annotation}.{_format}", methods={"DELETE"}, name="annotations_delete_annotation", defaults={"_format": "json"})
     * @ParamConverter("annotation", class="Wallabag\AnnotationBundle\Entity\Annotation")
     *
     * @return JsonResponse
     */
    public function deleteAnnotationAction(Annotation $annotation)
    {
        $em = $this->get('doctrine')->getManager();
        $em->remove($annotation);
        $em->flush();

        $json = $this->get(SerializerInterface::class)->serialize($annotation, 'json');

        return (new JsonResponse())->setJson($json);
    }
}
