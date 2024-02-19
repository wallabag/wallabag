<?php

namespace Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Entity\Annotation;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Form\Type\EditAnnotationType;
use Wallabag\Form\Type\NewAnnotationType;
use Wallabag\Repository\AnnotationRepository;

class AnnotationController extends AbstractFOSRestController
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
     * @see Api\WallabagRestController
     *
     * @Route("/annotations/{entry}.{_format}", methods={"GET"}, name="annotations_get_annotations", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function getAnnotationsAction(Entry $entry, AnnotationRepository $annotationRepository)
    {
        $annotationRows = $annotationRepository->findByEntryIdAndUserId($entry->getId(), $this->getUser()->getId());

        $total = \count($annotationRows);
        $annotations = ['total' => $total, 'rows' => $annotationRows];

        $json = $this->serializer->serialize($annotations, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Creates a new annotation.
     *
     * @see Api\WallabagRestController
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
     * @see Api\WallabagRestController
     *
     * @Route("/annotations/{annotation}.{_format}", methods={"PUT"}, name="annotations_put_annotation", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function putAnnotationAction(Request $request, AnnotationRepository $annotationRepository, int $annotation)
    {
        try {
            $annotation = $this->validateAnnotation($annotationRepository, $annotation, $this->getUser()->getId());

            $data = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

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
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e);
        }
    }

    /**
     * Removes an annotation.
     *
     * @see Api\WallabagRestController
     *
     * @Route("/annotations/{annotation}.{_format}", methods={"DELETE"}, name="annotations_delete_annotation", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function deleteAnnotationAction(AnnotationRepository $annotationRepository, int $annotation)
    {
        try {
            $annotation = $this->validateAnnotation($annotationRepository, $annotation, $this->getUser()->getId());

            $this->entityManager->remove($annotation);
            $this->entityManager->flush();

            $json = $this->serializer->serialize($annotation, 'json');

            return (new JsonResponse())->setJson($json);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e);
        }
    }

    /**
     * @return User|null
     */
    protected function getUser()
    {
        $user = parent::getUser();
        \assert(null === $user || $user instanceof User);

        return $user;
    }

    private function validateAnnotation(AnnotationRepository $annotationRepository, int $annotationId, int $userId)
    {
        $annotation = $annotationRepository->findOneByIdAndUserId($annotationId, $userId);

        if (null === $annotation) {
            throw new NotFoundHttpException();
        }

        return $annotation;
    }
}
