<?php

namespace Wallabag\AnnotationBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
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
use Wallabag\AnnotationBundle\Repository\AnnotationRepository;
use Wallabag\CoreBundle\Entity\Entry;

class WallabagAnnotationController extends AbstractFOSRestController
{
    protected EntityManagerInterface $entityManager;
    protected SerializerInterface $serializer;
    protected FormFactoryInterface $formFactory;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, FormFactoryInterface $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->formFactory = $formFactory;
    }

    /**
     * Retrieve annotations for an entry.
     *
     * @see Wallabag\ApiBundle\Controller\WallabagRestController
     *
     * @Route("/annotations/{entry}.{_format}", methods={"GET"}, name="annotations_get_annotations", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function getAnnotationsAction(Entry $entry, AnnotationRepository $annotationRepository)
    {
        $annotationRows = $annotationRepository->findAnnotationsByPageId($entry->getId(), $this->getUser()->getId());

        $total = \count($annotationRows);
        $annotations = ['total' => $total, 'rows' => $annotationRows];

        $json = $this->serializer->serialize($annotations, 'json');

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

        $annotation = new Annotation($this->getUser());
        $annotation->setEntry($entry);

        $form = $this->formFactory->createNamed('', NewAnnotationType::class, $annotation, [
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
        $form->submit($data);

        if ($form->isValid()) {
            $this->entityManager->persist($annotation);
            $this->entityManager->flush();

            $json = $this->serializer->serialize($annotation, 'json');

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

        $form = $this->formFactory->createNamed('', EditAnnotationType::class, $annotation, [
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
        $form->submit($data);

        if ($form->isValid()) {
            $this->entityManager->persist($annotation);
            $this->entityManager->flush();

            $json = $this->serializer->serialize($annotation, 'json');

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
        $this->entityManager->remove($annotation);
        $this->entityManager->flush();

        $json = $this->serializer->serialize($annotation, 'json');

        return (new JsonResponse())->setJson($json);
    }
}
