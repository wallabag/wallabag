<?php

namespace Wallabag\ApiBundle\Controller;

use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Event\EntryDeletedEvent;
use Wallabag\CoreBundle\Event\EntrySavedEvent;

class EntryRestController extends WallabagRestController
{
    /**
     * Check if an entry exist by url.
     * Return ID if entry(ies) exist (and if you give the return_id parameter).
     * Otherwise it returns false.
     *
     * @todo Remove that `return_id` in the next major release
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="return_id", "dataType"="string", "required"=false, "format"="1 or 0", "description"="Set 1 if you want to retrieve ID in case entry(ies) exists, 0 by default"},
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

        $returnId = (null === $request->query->get('return_id')) ? false : (bool) $request->query->get('return_id');
        $urls = $request->query->get('urls', []);

        // handle multiple urls first
        if (!empty($urls)) {
            $results = [];
            foreach ($urls as $url) {
                $res = $this->getDoctrine()
                    ->getRepository('WallabagCoreBundle:Entry')
                    ->findByUrlAndUserId($url, $this->getUser()->getId());

                $results[$url] = $this->returnExistInformation($res, $returnId);
            }

            return $this->sendResponse($results);
        }

        // let's see if it is a simple url?
        $url = $request->query->get('url', '');

        if (empty($url)) {
            throw $this->createAccessDeniedException('URL is empty?, logged user id: ' . $this->getUser()->getId());
        }

        $res = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($url, $this->getUser()->getId());

        $exists = $this->returnExistInformation($res, $returnId);

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
     *          {"name"="public", "dataType"="integer", "required"=false, "format"="1 or 0, all entries by default", "description"="filter by entries with a public link"},
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
        $isPublic = (null === $request->query->get('public')) ? null : (bool) $request->query->get('public');
        $sort = $request->query->get('sort', 'created');
        $order = $request->query->get('order', 'desc');
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 30);
        $tags = is_array($request->query->get('tags')) ? '' : (string) $request->query->get('tags', '');
        $since = $request->query->get('since', 0);

        /** @var \Pagerfanta\Pagerfanta $pager */
        $pager = $this->get('wallabag_core.entry_repository')->findEntries(
            $this->getUser()->getId(),
            $isArchived,
            $isStarred,
            $isPublic,
            $sort,
            $order,
            $since,
            $tags
        );

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
                    'public' => $isPublic,
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
            ->updateAuthor('entry')
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
     * @throws HttpException When limit is reached
     *
     * @return JsonResponse
     */
    public function postEntriesListAction(Request $request)
    {
        $this->validateAuthentication();

        $urls = json_decode($request->query->get('urls', []));

        $limit = $this->container->getParameter('wallabag_core.api_limit_mass_actions');

        if (count($urls) > $limit) {
            throw new HttpException(400, 'API limit reached');
        }

        $results = [];
        if (empty($urls)) {
            return $this->sendResponse($results);
        }

        // handle multiple urls
        foreach ($urls as $key => $url) {
            $entry = $this->get('wallabag_core.entry_repository')->findByUrlAndUserId(
                $url,
                $this->getUser()->getId()
            );

            $results[$key]['url'] = $url;

            if (false === $entry) {
                $entry = new Entry($this->getUser());

                $this->get('wallabag_core.content_proxy')->updateEntry($entry, $url);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();

            $results[$key]['entry'] = $entry instanceof Entry ? $entry->getId() : false;

            // entry saved, dispatch event about it!
            $this->get('event_dispatcher')->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));
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
     *          {"name"="archive", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="entry already archived"},
     *          {"name"="starred", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="entry already starred"},
     *          {"name"="content", "dataType"="string", "required"=false, "description"="Content of the entry"},
     *          {"name"="language", "dataType"="string", "required"=false, "description"="Language of the entry"},
     *          {"name"="preview_picture", "dataType"="string", "required"=false, "description"="Preview picture of the entry"},
     *          {"name"="published_at", "dataType"="datetime|integer", "format"="YYYY-MM-DDTHH:II:SS+TZ or a timestamp", "required"=false, "description"="Published date of the entry"},
     *          {"name"="authors", "dataType"="string", "format"="Name Firstname,author2,author3", "required"=false, "description"="Authors of the entry"},
     *          {"name"="public", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="will generate a public link for the entry"},
     *          {"name"="origin_url", "dataType"="string", "required"=false, "format"="http://www.test.com/article.html", "description"="Origin url for the entry (from where you found it)."},
     *       }
     * )
     *
     * @return JsonResponse
     */
    public function postEntriesAction(Request $request)
    {
        $this->validateAuthentication();

        $url = $request->request->get('url');

        $entry = $this->get('wallabag_core.entry_repository')->findByUrlAndUserId(
            $url,
            $this->getUser()->getId()
        );

        if (false === $entry) {
            $entry = new Entry($this->getUser());
            $entry->setUrl($url);
        }

        $data = $this->retrieveValueFromRequest($request);

        try {
            $this->get('wallabag_core.content_proxy')->updateEntry(
                $entry,
                $entry->getUrl(),
                [
                    'title' => !empty($data['title']) ? $data['title'] : $entry->getTitle(),
                    'html' => !empty($data['content']) ? $data['content'] : $entry->getContent(),
                    'url' => $entry->getUrl(),
                    'language' => !empty($data['language']) ? $data['language'] : $entry->getLanguage(),
                    'date' => !empty($data['publishedAt']) ? $data['publishedAt'] : $entry->getPublishedAt(),
                    // faking the open graph preview picture
                    'open_graph' => [
                        'og_image' => !empty($data['picture']) ? $data['picture'] : $entry->getPreviewPicture(),
                    ],
                    'authors' => is_string($data['authors']) ? explode(',', $data['authors']) : $entry->getPublishedBy(),
                ]
            );
        } catch (\Exception $e) {
            $this->get('logger')->error('Error while saving an entry', [
                'exception' => $e,
                'entry' => $entry,
            ]);
        }

        if (null !== $data['isArchived']) {
            $entry->setArchived((bool) $data['isArchived']);
        }

        if (null !== $data['isStarred']) {
            $entry->updateStar((bool) $data['isStarred']);
        }

        if (!empty($data['tags'])) {
            $this->get('wallabag_core.tags_assigner')->assignTagsToEntry($entry, $data['tags']);
        }

        if (!empty($data['origin_url'])) {
            $entry->setOriginUrl($data['origin_url']);
        }

        if (null !== $data['isPublic']) {
            if (true === (bool) $data['isPublic'] && null === $entry->getUid()) {
                $entry->generateUid();
            } elseif (false === (bool) $data['isPublic']) {
                $entry->cleanUid();
            }
        }

        if (empty($entry->getDomainName())) {
            $this->get('wallabag_core.content_proxy')->setEntryDomainName($entry);
        }

        if (empty($entry->getTitle())) {
            $this->get('wallabag_core.content_proxy')->setDefaultEntryTitle($entry);
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
     *          {"name"="content", "dataType"="string", "required"=false, "description"="Content of the entry"},
     *          {"name"="language", "dataType"="string", "required"=false, "description"="Language of the entry"},
     *          {"name"="preview_picture", "dataType"="string", "required"=false, "description"="Preview picture of the entry"},
     *          {"name"="published_at", "dataType"="datetime|integer", "format"="YYYY-MM-DDTHH:II:SS+TZ or a timestamp", "required"=false, "description"="Published date of the entry"},
     *          {"name"="authors", "dataType"="string", "format"="Name Firstname,author2,author3", "required"=false, "description"="Authors of the entry"},
     *          {"name"="public", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="will generate a public link for the entry"},
     *          {"name"="origin_url", "dataType"="string", "required"=false, "format"="http://www.test.com/article.html", "description"="Origin url for the entry (from where you found it)."},
     *      }
     * )
     *
     * @return JsonResponse
     */
    public function patchEntriesAction(Entry $entry, Request $request)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $contentProxy = $this->get('wallabag_core.content_proxy');

        $data = $this->retrieveValueFromRequest($request);

        // this is a special case where user want to manually update the entry content
        // the ContentProxy will only cleanup the html
        // and also we force to not re-fetch the content in case of error
        if (!empty($data['content'])) {
            try {
                $contentProxy->updateEntry(
                    $entry,
                    $entry->getUrl(),
                    [
                        'html' => $data['content'],
                    ],
                    true
                );
            } catch (\Exception $e) {
                $this->get('logger')->error('Error while saving an entry', [
                    'exception' => $e,
                    'entry' => $entry,
                ]);
            }
        }

        if (!empty($data['title'])) {
            $entry->setTitle($data['title']);
        }

        if (!empty($data['language'])) {
            $contentProxy->updateLanguage($entry, $data['language']);
        }

        if (!empty($data['authors']) && is_string($data['authors'])) {
            $entry->setPublishedBy(explode(',', $data['authors']));
        }

        if (!empty($data['picture'])) {
            $contentProxy->updatePreviewPicture($entry, $data['picture']);
        }

        if (!empty($data['publishedAt'])) {
            $contentProxy->updatePublishedAt($entry, $data['publishedAt']);
        }

        if (null !== $data['isArchived']) {
            $entry->setArchived((bool) $data['isArchived']);
        }

        if (null !== $data['isStarred']) {
            $entry->updateStar((bool) $data['isStarred']);
        }

        if (!empty($data['tags'])) {
            $entry->removeAllTags();
            $this->get('wallabag_core.tags_assigner')->assignTagsToEntry($entry, $data['tags']);
        }

        if (null !== $data['isPublic']) {
            if (true === (bool) $data['isPublic'] && null === $entry->getUid()) {
                $entry->generateUid();
            } elseif (false === (bool) $data['isPublic']) {
                $entry->cleanUid();
            }
        }

        if (!empty($data['origin_url'])) {
            $entry->setOriginUrl($data['origin_url']);
        }

        if (empty($entry->getDomainName())) {
            $this->get('wallabag_core.content_proxy')->setEntryDomainName($entry);
        }

        if (empty($entry->getTitle())) {
            $this->get('wallabag_core.content_proxy')->setDefaultEntryTitle($entry);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        // entry saved, dispatch event about it!
        $this->get('event_dispatcher')->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));

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
            $this->get('wallabag_core.content_proxy')->updateEntry($entry, $entry->getUrl());
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
        // https://github.com/schmittjoh/JMSSerializerBundle/issues/293
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $json = $this->get('jms_serializer')->serialize($data, 'json', $context);

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Retrieve value from the request.
     * Used for POST & PATCH on a an entry.
     *
     * @param Request $request
     *
     * @return array
     */
    private function retrieveValueFromRequest(Request $request)
    {
        return [
            'title' => $request->request->get('title'),
            'tags' => $request->request->get('tags', []),
            'isArchived' => $request->request->get('archive'),
            'isStarred' => $request->request->get('starred'),
            'isPublic' => $request->request->get('public'),
            'content' => $request->request->get('content'),
            'language' => $request->request->get('language'),
            'picture' => $request->request->get('preview_picture'),
            'publishedAt' => $request->request->get('published_at'),
            'authors' => $request->request->get('authors', ''),
            'origin_url' => $request->request->get('origin_url', ''),
        ];
    }

    /**
     * Return information about the entry if it exist and depending on the id or not.
     *
     * @param Entry|null $entry
     * @param bool       $returnId
     *
     * @return bool|int
     */
    private function returnExistInformation($entry, $returnId)
    {
        if ($returnId) {
            return $entry instanceof Entry ? $entry->getId() : null;
        }

        return $entry instanceof Entry;
    }
}
