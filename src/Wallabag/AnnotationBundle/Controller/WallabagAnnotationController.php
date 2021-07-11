<?php

namespace Wallabag\AnnotationBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\AnnotationBundle\Entity\Annotation;
use Wallabag\AnnotationBundle\Form\EditAnnotationType;
use Wallabag\AnnotationBundle\Form\NewAnnotationType;
use Wallabag\AnnotationBundle\Repository\AnnotationRepository;
use Wallabag\CoreBundle\Entity\Entry;

class WallabagAnnotationController extends AbstractFOSRestController
{
    /**
     * Retrieve annotations for an entry.
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @return JsonResponse
     *
     * @Get("/annotations/{entry}.{_format}", name="annotations_get_annotations")
     */
    public function getAnnotationsAction(Entry $entry)
    {
        $annotationRows = $this->get('wallabag_core.annotation_repository')
            ->findAnnotationsByPageId($entry->getId(), $this->getUser()->getId());
        $total = \count($annotationRows);
        $annotations = ['total' => $total, 'rows' => $annotationRows];

        $json = $this->get('jms_serializer')->serialize($annotations, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Creates a new annotation.
     *
     * @return JsonResponse
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @Post("/annotations/{entry}.{_format}", name="annotations_post_annotation")
     */
    public function postAnnotationAction(Request $request, Entry $entry)
    {
        $data = json_decode($request->getContent(), true);

        $em = $this->getDoctrine()->getManager();
        $annotation = new Annotation($this->getUser());
        $annotation->setEntry($entry);

        $form = $this->get('form.factory')->createNamed('', NewAnnotationType::class, $annotation, [
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
        $form->submit($data);

        if ($form->isValid()) {
            $em->persist($annotation);
            $em->flush();

            $json = $this->get('jms_serializer')->serialize($annotation, 'json');

            return JsonResponse::fromJsonString($json);
        }

        return $form;
    }

    /**
     * Updates an annotation.
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @ParamConverter("annotation", class="WallabagAnnotationBundle:Annotation")
     *
     * @return JsonResponse
     *
     * @Put("/annotations/{annotation}.{_format}", name="annotations_put_annotation")
     */
    public function putAnnotationAction(Annotation $annotation, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $form = $this->get('form.factory')->createNamed('', EditAnnotationType::class, $annotation, [
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
        $form->submit($data);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($annotation);
            $em->flush();

            $json = $this->get('jms_serializer')->serialize($annotation, 'json');

            return JsonResponse::fromJsonString($json);
        }

        return $form;
    }

    /**
     * Removes an annotation.
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @ParamConverter("annotation", class="WallabagAnnotationBundle:Annotation")
     *
     * @return JsonResponse
     *
     * @Delete("/annotations/{annotation}.{_format}", name="annotations_delete_annotation")
     */
    public function deleteAnnotationAction(Annotation $annotation)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($annotation);
        $em->flush();

        $json = $this->get('jms_serializer')->serialize($annotation, 'json');

        return (new JsonResponse())->setJson($json);
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'jms_serializer' => SerializerInterface::class,
                'wallabag_core.annotation_repository' => AnnotationRepository::class,
            ]
        );
    }
}
