<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Repository\EntryRepository;
use Wallabag\CoreBundle\Repository\TagRepository;

class TagRestController extends AbstractWallabagRestController
{
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Retrieve all tags.
     *
     * @ApiDoc()
     *
     * @return JsonResponse
     *
     * @Get(
     *  path="/api/tags.{_format}",
     *  name="api_get_tags",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
     */
    public function getTagsAction()
    {
        $this->validateAuthentication();

        $tags = $this->tagRepository
            ->findAllTags($this->getUser()->getId());

        $json = $this->get('jms_serializer')->serialize($tags, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Permanently remove one tag from **every** entry by passing the Tag label.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="tag", "dataType"="string", "required"=true, "requirement"="\w+", "description"="Tag as a string"}
     *      }
     * )
     *
     * @return JsonResponse
     *
     * @Delete(
     *  path="/api/tag/label.{_format}",
     *  name="api_delete_tag_label",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
     */
    public function deleteTagLabelAction(Request $request)
    {
        $this->validateAuthentication();
        $label = $request->get('tag', '');

        $tags = $this->tagRepository->findByLabelsAndUser([$label], $this->getUser()->getId());

        if (empty($tags)) {
            throw $this->createNotFoundException('Tag not found');
        }

        $tag = $tags[0];

        $this->get('wallabag_core.entry_repository')
            ->removeTag($this->getUser()->getId(), $tag);

        $this->cleanOrphanTag($tag);

        $json = $this->get('jms_serializer')->serialize($tag, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Permanently remove some tags from **every** entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="tags", "dataType"="string", "required"=true, "format"="tag1,tag2", "description"="Tags as strings (comma splitted)"}
     *      }
     * )
     *
     * @return JsonResponse
     *
     * @Delete(
     *  path="/api/tags/label.{_format}",
     *  name="api_delete_tags_label",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
     */
    public function deleteTagsLabelAction(Request $request)
    {
        $this->validateAuthentication();

        $tagsLabels = $request->get('tags', '');

        $tags = $this->tagRepository->findByLabelsAndUser(explode(',', $tagsLabels), $this->getUser()->getId());

        if (empty($tags)) {
            throw $this->createNotFoundException('Tags not found');
        }

        $this->get('wallabag_core.entry_repository')
            ->removeTags($this->getUser()->getId(), $tags);

        $this->cleanOrphanTag($tags);

        $json = $this->get('jms_serializer')->serialize($tags, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Permanently remove one tag from **every** entry by passing the Tag ID.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="tag", "dataType"="integer", "requirement"="\w+", "description"="The tag"}
     *      }
     * )
     *
     * @return JsonResponse
     *
     * @Delete(
     *  path="/api/tags/{tag}.{_format}",
     *  name="api_delete_tag",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
     */
    public function deleteTagAction(Tag $tag)
    {
        $this->validateAuthentication();

        $tagFromDb = $this->tagRepository->findByLabelsAndUser([$tag->getLabel()], $this->getUser()->getId());

        if (empty($tagFromDb)) {
            throw $this->createNotFoundException('Tag not found');
        }

        $this->get('wallabag_core.entry_repository')
            ->removeTag($this->getUser()->getId(), $tag);

        $this->cleanOrphanTag($tag);

        $json = $this->get('jms_serializer')->serialize($tag, 'json');

        return (new JsonResponse())->setJson($json);
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'wallabag_core.entry_repository' => EntryRepository::class,
            ]
        );
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
