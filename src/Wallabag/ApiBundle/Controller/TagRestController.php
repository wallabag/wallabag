<?php

namespace Wallabag\ApiBundle\Controller;

use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;

class TagRestController extends WallabagRestController
{
    /**
     * Retrieve all tags.
     *
     * @Operation(
     *     tags={"Tags"},
     *     summary="Retrieve all tags.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/tags.{_format}", methods={"GET"}, name="api_get_tags", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function getTagsAction()
    {
        $this->validateAuthentication();

        $tags = $this->getDoctrine()
            ->getRepository(Tag::class)
            ->findAllFlatTagsWithNbEntries($this->getUser()->getId());

        $json = $this->get(SerializerInterface::class)->serialize($tags, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Permanently remove one tag from **every** entry by passing the Tag label.
     *
     * @Operation(
     *     tags={"Tags"},
     *     summary="Permanently remove one tag from every entry by passing the Tag label.",
     *     @SWG\Parameter(
     *         name="tag",
     *         in="body",
     *         description="Tag as a string",
     *         required=true,
     *         pattern="\w+",
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/tag/label.{_format}", methods={"DELETE"}, name="api_delete_tag_label", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function deleteTagLabelAction(Request $request)
    {
        $this->validateAuthentication();
        $label = $request->get('tag', '');

        $tags = $this->getDoctrine()->getRepository(Tag::class)->findByLabelsAndUser([$label], $this->getUser()->getId());

        if (empty($tags)) {
            throw $this->createNotFoundException('Tag not found');
        }

        $tag = $tags[0];

        $this->getDoctrine()
            ->getRepository(Entry::class)
            ->removeTag($this->getUser()->getId(), $tag);

        $this->cleanOrphanTag($tag);

        $json = $this->get(SerializerInterface::class)->serialize($tag, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Permanently remove some tags from **every** entry.
     *
     * @Operation(
     *     tags={"Tags"},
     *     summary="Permanently remove some tags from every entry.",
     *     @SWG\Parameter(
     *         name="tags",
     *         in="body",
     *         description="Tags as strings (comma splitted)",
     *         required=true,
     *         @SWG\Schema(
     *             type="string",
     *             example="tag1,tag2",
     *         )
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/tags/label.{_format}", methods={"DELETE"}, name="api_delete_tags_label", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function deleteTagsLabelAction(Request $request)
    {
        $this->validateAuthentication();

        $tagsLabels = $request->get('tags', '');

        $tags = $this->getDoctrine()->getRepository(Tag::class)->findByLabelsAndUser(explode(',', $tagsLabels), $this->getUser()->getId());

        if (empty($tags)) {
            throw $this->createNotFoundException('Tags not found');
        }

        $this->getDoctrine()
            ->getRepository(Entry::class)
            ->removeTags($this->getUser()->getId(), $tags);

        $this->cleanOrphanTag($tags);

        $json = $this->get(SerializerInterface::class)->serialize($tags, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Permanently remove one tag from **every** entry by passing the Tag ID.
     *
     * @Operation(
     *     tags={"Tags"},
     *     summary="Permanently remove one tag from every entry by passing the Tag ID.",
     *     @SWG\Parameter(
     *         name="tag",
     *         in="body",
     *         description="The tag",
     *         required=true,
     *         pattern="\w+",
     *         @SWG\Schema(type="integer")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/tags/{tag}.{_format}", methods={"DELETE"}, name="api_delete_tag", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function deleteTagAction(Tag $tag)
    {
        $this->validateAuthentication();

        $tagFromDb = $this->getDoctrine()->getRepository(Tag::class)->findByLabelsAndUser([$tag->getLabel()], $this->getUser()->getId());

        if (empty($tagFromDb)) {
            throw $this->createNotFoundException('Tag not found');
        }

        $this->getDoctrine()
            ->getRepository(Entry::class)
            ->removeTag($this->getUser()->getId(), $tag);

        $this->cleanOrphanTag($tag);

        $json = $this->get(SerializerInterface::class)->serialize($tag, 'json');

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

        $em = $this->getDoctrine()->getManager();

        foreach ($tags as $tag) {
            if (0 === \count($tag->getEntries())) {
                $em->remove($tag);
            }
        }

        $em->flush();
    }
}
