<?php

namespace Wallabag\Controller\Api;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Entity\Tag;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\TagRepository;

class TagRestController extends WallabagRestController
{
    #[OA\Get(
        summary: 'Retrieve all tags.',
        tags: ['Tags'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returned when successful.',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Tag::class))
                )
            ),
        ]
    )]
    #[Route(path: '/api/tags.{_format}', name: 'api_get_tags', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getTagsAction(TagRepository $tagRepository)
    {
        $this->validateAuthentication();

        $tags = $tagRepository->findAllFlatTagsWithNbEntries($this->getUser()->getId());

        $json = $this->serializer->serialize($tags, 'json');

        return (new JsonResponse())->setJson($json);
    }

    #[OA\Delete(
        summary: 'Permanently remove one tag from every entry by passing the Tag label.',
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(
                name: 'tag',
                in: 'query',
                description: 'Tag as a string.',
                required: true,
                schema: new OA\Schema(type: 'string', pattern: '\w+')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Returned when successful.'),
        ]
    )]
    #[Route(path: '/api/tag/label.{_format}', name: 'api_delete_tag_label', methods: ['DELETE'], defaults: ['_format' => 'json'])]
    public function deleteTagLabelAction(Request $request, TagRepository $tagRepository, EntryRepository $entryRepository)
    {
        $this->validateAuthentication();
        $label = $request->request->get('tag', $request->query->get('tag', ''));

        $tags = $tagRepository->findByLabelsAndUser([$label], $this->getUser()->getId());

        if (empty($tags)) {
            throw $this->createNotFoundException('Tag not found');
        }

        $tag = $tags[0];

        $entryRepository->removeTag($this->getUser()->getId(), $tag);

        $this->cleanOrphanTag($tag);

        $json = $this->serializer->serialize($tag, 'json');

        return (new JsonResponse())->setJson($json);
    }

    #[OA\Delete(
        summary: 'Permanently remove some tags from every entry.',
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(
                name: 'tags',
                in: 'query',
                description: 'Tags as comma-separated strings.',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'tag1,tag2')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Returned when successful.'),
        ]
    )]
    #[Route(path: '/api/tags/label.{_format}', name: 'api_delete_tags_label', methods: ['DELETE'], defaults: ['_format' => 'json'])]
    public function deleteTagsLabelAction(Request $request, TagRepository $tagRepository, EntryRepository $entryRepository)
    {
        $this->validateAuthentication();

        $tagsLabels = $request->request->get('tags', $request->query->get('tags', ''));

        $tags = $tagRepository->findByLabelsAndUser(explode(',', $tagsLabels), $this->getUser()->getId());

        if (empty($tags)) {
            throw $this->createNotFoundException('Tags not found');
        }

        $entryRepository->removeTags($this->getUser()->getId(), $tags);

        $this->cleanOrphanTag($tags);

        $json = $this->serializer->serialize($tags, 'json');

        return (new JsonResponse())->setJson($json);
    }

    #[OA\Delete(
        summary: 'Permanently remove one tag from every entry by passing the Tag ID.',
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(
                name: 'tag',
                in: 'path',
                description: 'The tag ID.',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Returned when successful.'),
        ]
    )]
    #[Route(path: '/api/tags/{tag}.{_format}', name: 'api_delete_tag', methods: ['DELETE'], defaults: ['_format' => 'json'])]
    public function deleteTagAction(Tag $tag, TagRepository $tagRepository, EntryRepository $entryRepository)
    {
        $this->validateAuthentication();

        $tagFromDb = $tagRepository->findByLabelsAndUser([$tag->getLabel()], $this->getUser()->getId());

        if (empty($tagFromDb)) {
            throw $this->createNotFoundException('Tag not found');
        }

        $entryRepository->removeTag($this->getUser()->getId(), $tag);

        $this->cleanOrphanTag($tag);

        $json = $this->serializer->serialize($tag, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Remove orphan tag in case no entries are associated to it.
     *
     * @param Tag|array $tags
     */
    private function cleanOrphanTag($tags)
    {
        if (!\is_array($tags)) {
            $tags = [$tags];
        }

        foreach ($tags as $tag) {
            if (0 === \count($tag->getEntries())) {
                $this->entityManager->remove($tag);
            }
        }

        $this->entityManager->flush();
    }
}
