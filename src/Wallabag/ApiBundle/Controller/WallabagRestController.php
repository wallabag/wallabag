<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;

class WallabagRestController extends FOSRestController
{
    private function validateAuthentication()
    {
        if (false === $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Retrieve all entries. It could be filtered by many options.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="archive", "dataType"="integer", "required"=false, "format"="1 or 0, all entries by default", "description"="filter by archived status."},
     *          {"name"="starred", "dataType"="integer", "required"=false, "format"="1 or 0, all entries by default", "description"="filter by starred status."},
     *          {"name"="sort", "dataType"="string", "required"=false, "format"="'created' or 'updated', default 'created'", "description"="sort entries by date."},
     *          {"name"="order", "dataType"="string", "required"=false, "format"="'asc' or 'desc', default 'desc'", "description"="order of sort."},
     *          {"name"="page", "dataType"="integer", "required"=false, "format"="default '1'", "description"="what page you want."},
     *          {"name"="perPage", "dataType"="integer", "required"=false, "format"="default'30'", "description"="results per page."},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="api,rest", "description"="a list of tags url encoded. Will returns entries that matches ALL tags."},
     *          {"name"="since", "dataType"="integer", "required"=false, "format"="default '0'", "description"="The timestamp since when you want entries updated."},
     *       }
     * )
     *
     * @return JsonResponse
     */
    public function getEntriesAction(Request $request)
    {
        $this->validateAuthentication();

        $isArchived = (null === $request->query->get('archive')) ? null : (bool) $request->query->get('archive');
        $isStarred = (null === $request->query->get('starred')) ? null : (bool) $request->query->get('starred');
        $sort = $request->query->get('sort', 'created');
        $order = $request->query->get('order', 'desc');
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 30);
        $since = $request->query->get('since', 0);
        $tags = $request->query->get('tags', '');

        $pager = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findEntries($this->getUser()->getId(), $isArchived, $isStarred, $sort, $order, $since, $tags);

        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($perPage);

        $pagerfantaFactory = new PagerfantaFactory('page', 'perPage');
        $paginatedCollection = $pagerfantaFactory->createRepresentation(
            $pager,
            new Route('api_get_entries', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        $json = $this->get('serializer')->serialize($paginatedCollection, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Retrieve a single entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function getEntryAction(Entry $entry)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $json = $this->get('serializer')->serialize($entry, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Create an entry.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="url", "dataType"="string", "required"=true, "format"="http://www.test.com/article.html", "description"="Url for the entry."},
     *          {"name"="title", "dataType"="string", "required"=false, "description"="Optional, we'll get the title from the page."},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="tag1,tag2,tag3", "description"="a comma-separated list of tags."},
     *          {"name"="starred", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="entry already starred"},
     *          {"name"="archive", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="entry already archived"},
     *       }
     * )
     *
     * @return JsonResponse
     */
    public function postEntriesAction(Request $request)
    {
        $this->validateAuthentication();

        $url = $request->request->get('url');
        $title = $request->request->get('title');
        $isArchived = $request->request->get('archive');
        $isStarred = $request->request->get('starred');

        $entry = $this->get('wallabag_core.entry_repository')->findByUrlAndUserId($url, $this->getUser()->getId());

        if (false === $entry) {
            $entry = $this->get('wallabag_core.content_proxy')->updateEntry(
                new Entry($this->getUser()),
                $url
            );
        }

        if (!is_null($title)) {
            $entry->setTitle($title);
        }

        $tags = $request->request->get('tags', '');
        if (!empty($tags)) {
            $this->get('wallabag_core.content_proxy')->assignTagsToEntry($entry, $tags);
        }

        if (!is_null($isStarred)) {
            $entry->setStarred((bool) $isStarred);
        }

        if (!is_null($isArchived)) {
            $entry->setArchived((bool) $isArchived);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);

        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Change several properties of an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      },
     *      parameters={
     *          {"name"="title", "dataType"="string", "required"=false},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="tag1,tag2,tag3", "description"="a comma-separated list of tags."},
     *          {"name"="archive", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="archived the entry."},
     *          {"name"="starred", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="starred the entry."},
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function patchEntriesAction(Entry $entry, Request $request)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $title = $request->request->get('title');
        $isArchived = $request->request->get('archive');
        $isStarred = $request->request->get('starred');

        if (!is_null($title)) {
            $entry->setTitle($title);
        }

        if (!is_null($isArchived)) {
            $entry->setArchived((bool) $isArchived);
        }

        if (!is_null($isStarred)) {
            $entry->setStarred((bool) $isStarred);
        }

        $tags = $request->request->get('tags', '');
        if (!empty($tags)) {
            $this->get('wallabag_core.content_proxy')->assignTagsToEntry($entry, $tags);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Delete **permanently** an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function deleteEntriesAction(Entry $entry)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $em = $this->getDoctrine()->getManager();
        $em->remove($entry);
        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Retrieve all tags for an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function getEntriesTagsAction(Entry $entry)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $json = $this->get('serializer')->serialize($entry->getTags(), 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Add one or more tags to an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      },
     *      parameters={
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="tag1,tag2,tag3", "description"="a comma-separated list of tags."},
     *       }
     * )
     *
     * @return JsonResponse
     */
    public function postEntriesTagsAction(Request $request, Entry $entry)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $tags = $request->request->get('tags', '');
        if (!empty($tags)) {
            $this->get('wallabag_core.content_proxy')->assignTagsToEntry($entry, $tags);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Permanently remove one tag for an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="tag", "dataType"="integer", "requirement"="\w+", "description"="The tag ID"},
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function deleteEntriesTagsAction(Entry $entry, Tag $tag)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $entry->removeTag($tag);
        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return (new JsonResponse())->setJson($json);
    }

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
            ->findAllTagsWithEntries($this->getUser()->getId());

        $json = $this->get('serializer')->serialize($tags, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Permanently remove one tag from **every** entry.
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
        $label = $request->request->get('tag', '');

        $tag = $this->getDoctrine()->getRepository('WallabagCoreBundle:Tag')->findOneByLabel($label);

        if (empty($tag)) {
            throw $this->createNotFoundException('Tag not found');
        }

        $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->removeTag($this->getUser()->getId(), $tag);

        $json = $this->get('serializer')->serialize($tag, 'json');

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

        $tagsLabels = $request->request->get('tags', '');

        $tags = [];

        foreach (explode(',', $tagsLabels) as $tagLabel) {
            $tagEntity = $this->getDoctrine()->getRepository('WallabagCoreBundle:Tag')->findOneByLabel($tagLabel);

            if (!empty($tagEntity)) {
                $tags[] = $tagEntity;
            }
        }

        if (empty($tags)) {
            throw $this->createNotFoundException('Tags not found');
        }

        $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->removeTags($this->getUser()->getId(), $tags);

        $json = $this->get('serializer')->serialize($tags, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Permanently remove one tag from **every** entry.
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

        $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->removeTag($this->getUser()->getId(), $tag);

        $json = $this->get('serializer')->serialize($tag, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Retrieve version number.
     *
     * @ApiDoc()
     *
     * @return JsonResponse
     */
    public function getVersionAction()
    {
        $version = $this->container->getParameter('wallabag_core.version');

        $json = $this->get('serializer')->serialize($version, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Validate that the first id is equal to the second one.
     * If not, throw exception. It means a user try to access information from an other user.
     *
     * @param int $requestUserId User id from the requested source
     */
    private function validateUserAccess($requestUserId)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($requestUserId != $user->getId()) {
            throw $this->createAccessDeniedException('Access forbidden. Entry user id: '.$requestUserId.', logged user id: '.$user->getId());
        }
    }
}
