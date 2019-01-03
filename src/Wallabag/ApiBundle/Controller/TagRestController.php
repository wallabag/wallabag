<?php

namespace Wallabag\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;

class TagRestController extends WallabagRestController
{
    /**
     * Retrieve all tags.
     *
     * @ApiDoc()
     *
     * @return JsonResponse
     */
    public function getTagsAction()
    {
        $this->validateAuthentication();

        $tags = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Tag')
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
     */
    public function deleteTagLabelAction(Request $request)
    {
        $this->validateAuthentication();
        $label = $request->get('tag', '');

        $tags = $this->getDoctrine()->getRepository('WallabagCoreBundle:Tag')->findByLabelsAndUser([$label], $this->getUser()->getId());

        if (empty($tags)) {
            throw $this->createNotFoundException('Tag not found');
        }

        $tag = $tags[0];

        $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
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
     */
    public function deleteTagsLabelAction(Request $request)
    {
        $this->validateAuthentication();

        $tagsLabels = $request->get('tags', '');

        $tags = $this->getDoctrine()->getRepository('WallabagCoreBundle:Tag')->findByLabelsAndUser(explode(',', $tagsLabels), $this->getUser()->getId());

        if (empty($tags)) {
            throw $this->createNotFoundException('Tags not found');
        }

        $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
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
     */
    public function deleteTagAction(Tag $tag)
    {
        $this->validateAuthentication();

        $tagFromDb = $this->getDoctrine()->getRepository('WallabagCoreBundle:Tag')->findByLabelsAndUser([$tag->getLabel()], $this->getUser()->getId());

        if (empty($tagFromDb)) {
            throw $this->createNotFoundException('Tag not found');
        }

        $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->removeTag($this->getUser()->getId(), $tag);

        $this->cleanOrphanTag($tag);

        $json = $this->get('jms_serializer')->serialize($tag, 'json');

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
