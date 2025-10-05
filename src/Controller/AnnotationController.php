<?php

namespace Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected SerializerInterface $serializer,
        protected FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * Retrieve annotations for an entry.
     *
     * @see Api\WallabagRestController
     *
     * @return JsonResponse
     */
    #[Route(path: '/annotations/{entry}.{_format}', name: 'annotations_get_annotations', methods: ['GET'], defaults: ['_format' => 'json'])]
    #[IsGranted('LIST_ANNOTATIONS', subject: 'entry')]
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
     * @return JsonResponse
     */
    #[Route(path: '/annotations/{entry}.{_format}', name: 'annotations_post_annotation', methods: ['POST'], defaults: ['_format' => 'json'])]
    #[IsGranted('CREATE_ANNOTATIONS', subject: 'entry')]
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

        return new JsonResponse(status: 400);
    }

    /**
     * Updates an annotation.
     *
     * @see Api\WallabagRestController
     *
     * @return JsonResponse
     */
    #[Route(path: '/annotations/{annotation}.{_format}', name: 'annotations_put_annotation', methods: ['PUT'], defaults: ['_format' => 'json'])]
    #[IsGranted('EDIT', subject: 'annotation')]
    public function putAnnotationAction(Request $request, Annotation $annotation)
    {
        try {
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

            return new JsonResponse(status: 400);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e);
        }
    }

    /**
     * Removes an annotation.
     *
     * @see Api\WallabagRestController
     *
     * @return JsonResponse
     */
    #[Route(path: '/annotations/{annotation}.{_format}', name: 'annotations_delete_annotation', methods: ['DELETE'], defaults: ['_format' => 'json'])]
    #[IsGranted('DELETE', subject: 'annotation')]
    public function deleteAnnotationAction(Annotation $annotation)
    {
        try {
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
}
