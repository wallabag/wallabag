<?php

namespace Wallabag\ApiBundle\Controller;

use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Event\EntrySavedEvent;
use Wallabag\CoreBundle\Event\EntryDeletedEvent;

class EntryRestController extends WallabagRestController
{
    /**
     * Check if an entry exist by url.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="url", "dataType"="string", "required"=true, "format"="An url", "description"="Url to check if it exists"},
     *          {"name"="urls", "dataType"="string", "required"=false, "format"="An array of urls (?urls[]=http...&urls[]=http...)", "description"="Urls (as an array) to check if it exists"}
     *       }
     * )
     *
     * @return JsonResponse
     */
    public function getEntriesExistsAction(Request $request)
    {
        $this->validateAuthentication();

        $urls = $request->query->get('urls', []);

        // handle multiple urls first
        if (!empty($urls)) {
            $results = [];
            foreach ($urls as $url) {
                $res = $this->getDoctrine()
                    ->getRepository('WallabagCoreBundle:Entry')
                    ->findByUrlAndUserId($url, $this->getUser()->getId());

                $results[$url] = $res instanceof Entry ? $res->getId() : false;
            }

            return $this->sendResponse($results);
        }

        // let's see if it is a simple url?
        $url = $request->query->get('url', '');

        if (empty($url)) {
            throw $this->createAccessDeniedException('URL is empty?, logged user id: '.$this->getUser()->getId());
        }

        $res = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($url, $this->getUser()->getId());

        $exists = $res instanceof Entry ? $res->getId() : false;

        return $this->sendResponse(['exists' => $exists]);
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
        $tags = $request->query->get('tags', '');
        $since = $request->query->get('since', 0);

        /** @var \Pagerfanta\Pagerfanta $pager */
        $pager = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findEntries($this->getUser()->getId(), $isArchived, $isStarred, $sort, $order, $since, $tags);

        $pager->setMaxPerPage($perPage);
        $pager->setCurrentPage($page);

        $pagerfantaFactory = new PagerfantaFactory('page', 'perPage');
        $paginatedCollection = $pagerfantaFactory->createRepresentation(
            $pager,
            new Route(
                'api_get_entries',
                [
                    'archive' => $isArchived,
                    'starred' => $isStarred,
                    'sort' => $sort,
                    'order' => $order,
                    'page' => $page,
                    'perPage' => $perPage,
                    'tags' => $tags,
                    'since' => $since,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );

        return $this->sendResponse($paginatedCollection);
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

        return $this->sendResponse($entry);
    }

    /**
     * Retrieve a single entry as a predefined format.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return Response
     */
    public function getEntryExportAction(Entry $entry, Request $request)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        return $this->get('wallabag_core.helper.entries_export')
            ->setEntries($entry)
            ->updateTitle('entry')
            ->exportAs($request->attributes->get('_format'));
    }

    /**
     * Handles an entries list and delete URL.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="urls", "dataType"="string", "required"=true, "format"="A JSON array of urls [{'url': 'http://...'}, {'url': 'http://...'}]", "description"="Urls (as an array) to delete."}
     *       }
     * )
     *
     * @return JsonResponse
     */
    public function deleteEntriesListAction(Request $request)
    {
        $this->validateAuthentication();

        $urls = json_decode($request->query->get('urls', []));

        if (empty($urls)) {
            return $this->sendResponse([]);
        }

        $results = [];

        // handle multiple urls
        foreach ($urls as $key => $url) {
            $entry = $this->get('wallabag_core.entry_repository')->findByUrlAndUserId(
                $url,
                $this->getUser()->getId()
            );

            $results[$key]['url'] = $url;

            if (false !== $entry) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($entry);
                $em->flush();

                // entry deleted, dispatch event about it!
                $this->get('event_dispatcher')->dispatch(EntryDeletedEvent::NAME, new EntryDeletedEvent($entry));
            }

            $results[$key]['entry'] = $entry instanceof Entry ? true : false;
        }

        return $this->sendResponse($results);
    }

    /**
     * Handles an entries list and create URL.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="urls", "dataType"="string", "required"=true, "format"="A JSON array of urls [{'url': 'http://...'}, {'url': 'http://...'}]", "description"="Urls (as an array) to create."}
     *       }
     * )
     *
     * @return JsonResponse
     *
     * @throws HttpException When limit is reached
     */
    public function postEntriesListAction(Request $request)
    {
        $this->validateAuthentication();

        $urls = json_decode($request->query->get('urls', []));
        $results = [];

        $limit = $this->container->getParameter('wallabag_core.api_limit_mass_actions');

        if (count($urls) > $limit) {
            throw new HttpException(400, 'API limit reached');
        }

        // handle multiple urls
        if (!empty($urls)) {
            foreach ($urls as $key => $url) {
                $entry = $this->get('wallabag_core.entry_repository')->findByUrlAndUserId(
                    $url,
                    $this->getUser()->getId()
                );

                $results[$key]['url'] = $url;

                if (false === $entry) {
                    $entry = $this->get('wallabag_core.content_proxy')->updateEntry(
                        new Entry($this->getUser()),
                        $url
                    );
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($entry);
                $em->flush();

                $results[$key]['entry'] = $entry instanceof Entry ? $entry->getId() : false;

                // entry saved, dispatch event about it!
                $this->get('event_dispatcher')->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));
            }
        }

        return $this->sendResponse($results);
    }

    /**
     * Create an entry.
     *
     * If you want to provide the HTML content (which means wallabag won't fetch it from the url), you must provide `content`, `title` & `url` fields **non-empty**.
     * Otherwise, content will be fetched as normal from the url and values will be overwritten.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="url", "dataType"="string", "required"=true, "format"="http://www.test.com/article.html", "description"="Url for the entry."},
     *          {"name"="title", "dataType"="string", "required"=false, "description"="Optional, we'll get the title from the page."},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="tag1,tag2,tag3", "description"="a comma-separated list of tags."},
     *          {"name"="starred", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="entry already starred"},
     *          {"name"="archive", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="entry already archived"},
     *          {"name"="content", "dataType"="string", "required"=false, "description"="Content of the entry"},
     *          {"name"="language", "dataType"="string", "required"=false, "description"="Language of the entry"},
     *          {"name"="preview_picture", "dataType"="string", "required"=false, "description"="Preview picture of the entry"},
     *          {"name"="published_at", "dataType"="datetime", "format"="YYYY-MM-DDTHH:II:SS+TZ", "required"=false, "description"="Published date of the entry"},
     *          {"name"="authors", "dataType"="string", "format"="Name Firstname,author2,author3", "required"=false, "description"="Authors of the entry"},
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
        $tags = $request->request->get('tags', []);
        $isArchived = $request->request->get('archive');
        $isStarred = $request->request->get('starred');
        $content = $request->request->get('content');
        $language = $request->request->get('language');
        $picture = $request->request->get('preview_picture');
        $publishedAt = $request->request->get('published_at');
        $authors = $request->request->get('authors', '');

        $entry = $this->get('wallabag_core.entry_repository')->findByUrlAndUserId($url, $this->getUser()->getId());

        if (false === $entry) {
            $entry = new Entry($this->getUser());
        }

        try {
            $entry = $this->get('wallabag_core.content_proxy')->updateEntry(
                $entry,
                $url,
                [
                    'title' => $title,
                    'html' => $content,
                    'url' => $url,
                    'language' => $language,
                    'date' => $publishedAt,
                    // faking the preview picture
                    'open_graph' => [
                        'og_image' => $picture,
                    ],
                    'authors' => explode(',', $authors),
                ]
            );
        } catch (\Exception $e) {
            $this->get('logger')->error('Error while saving an entry', [
                'exception' => $e,
                'entry' => $entry,
            ]);
            $entry->setUrl($url);
        }

        if (!empty($tags)) {
            $this->get('wallabag_core.tags_assigner')->assignTagsToEntry($entry, $tags);
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

        // entry saved, dispatch event about it!
        $this->get('event_dispatcher')->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));

        return $this->sendResponse($entry);
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
            $this->get('wallabag_core.tags_assigner')->assignTagsToEntry($entry, $tags);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->sendResponse($entry);
    }

    /**
     * Reload an entry.
     * An empty response with HTTP Status 304 will be send if we weren't able to update the content (because it hasn't changed or we got an error).
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function patchEntriesReloadAction(Entry $entry)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        try {
            $entry = $this->get('wallabag_core.content_proxy')->updateEntry($entry, $entry->getUrl());
        } catch (\Exception $e) {
            $this->get('logger')->error('Error while saving an entry', [
                'exception' => $e,
                'entry' => $entry,
            ]);

            return new JsonResponse([], 304);
        }

        // if refreshing entry failed, don't save it
        if ($this->getParameter('wallabag_core.fetching_error_message') === $entry->getContent()) {
            return new JsonResponse([], 304);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        // entry saved, dispatch event about it!
        $this->get('event_dispatcher')->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));

        return $this->sendResponse($entry);
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

        // entry deleted, dispatch event about it!
        $this->get('event_dispatcher')->dispatch(EntryDeletedEvent::NAME, new EntryDeletedEvent($entry));

        return $this->sendResponse($entry);
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

        return $this->sendResponse($entry->getTags());
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
            $this->get('wallabag_core.tags_assigner')->assignTagsToEntry($entry, $tags);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        return $this->sendResponse($entry);
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

        return $this->sendResponse($entry);
    }

    /**
     * Handles an entries list delete tags from them.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="list", "dataType"="string", "required"=true, "format"="A JSON array of urls [{'url': 'http://...','tags': 'tag1, tag2'}, {'url': 'http://...','tags': 'tag1, tag2'}]", "description"="Urls (as an array) to handle."}
     *       }
     * )
     *
     * @return JsonResponse
     */
    public function deleteEntriesTagsListAction(Request $request)
    {
        $this->validateAuthentication();

        $list = json_decode($request->query->get('list', []));

        if (empty($list)) {
            return $this->sendResponse([]);
        }

        // handle multiple urls
        $results = [];

        foreach ($list as $key => $element) {
            $entry = $this->get('wallabag_core.entry_repository')->findByUrlAndUserId(
                $element->url,
                $this->getUser()->getId()
            );

            $results[$key]['url'] = $element->url;
            $results[$key]['entry'] = $entry instanceof Entry ? $entry->getId() : false;

            $tags = $element->tags;

            if (false !== $entry && !(empty($tags))) {
                $tags = explode(',', $tags);
                foreach ($tags as $label) {
                    $label = trim($label);

                    $tag = $this->getDoctrine()
                        ->getRepository('WallabagCoreBundle:Tag')
                        ->findOneByLabel($label);

                    if (false !== $tag) {
                        $entry->removeTag($tag);
                    }
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($entry);
                $em->flush();
            }
        }

        return $this->sendResponse($results);
    }

    /**
     * Handles an entries list and add tags to them.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="list", "dataType"="string", "required"=true, "format"="A JSON array of urls [{'url': 'http://...','tags': 'tag1, tag2'}, {'url': 'http://...','tags': 'tag1, tag2'}]", "description"="Urls (as an array) to handle."}
     *       }
     * )
     *
     * @return JsonResponse
     */
    public function postEntriesTagsListAction(Request $request)
    {
        $this->validateAuthentication();

        $list = json_decode($request->query->get('list', []));

        if (empty($list)) {
            return $this->sendResponse([]);
        }

        $results = [];

        // handle multiple urls
        foreach ($list as $key => $element) {
            $entry = $this->get('wallabag_core.entry_repository')->findByUrlAndUserId(
                $element->url,
                $this->getUser()->getId()
            );

            $results[$key]['url'] = $element->url;
            $results[$key]['entry'] = $entry instanceof Entry ? $entry->getId() : false;

            $tags = $element->tags;

            if (false !== $entry && !(empty($tags))) {
                $this->get('wallabag_core.tags_assigner')->assignTagsToEntry($entry, $tags);

                $em = $this->getDoctrine()->getManager();
                $em->persist($entry);
                $em->flush();
            }
        }

        return $this->sendResponse($results);
    }

    /**
     * Shortcut to send data serialized in json.
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    private function sendResponse($data)
    {
        $json = $this->get('serializer')->serialize($data, 'json');

        return (new JsonResponse())->setJson($json);
    }
}
