<?php

namespace Wallabag\Controller\Api;

use Hateoas\Configuration\Route as HateoasRoute;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Nelmio\ApiDocBundle\Annotation\Operation;
use OpenApi\Annotations as OA;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\Tag;
use Wallabag\Event\EntryDeletedEvent;
use Wallabag\Event\EntrySavedEvent;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\EntriesExport;
use Wallabag\Helper\TagsAssigner;
use Wallabag\Helper\UrlHasher;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\TagRepository;

class EntryRestController extends WallabagRestController
{
    /**
     * Check if an entry exist by url.
     * Return ID if entry(ies) exist (and if you give the return_id parameter).
     * Otherwise it returns false.
     *
     * @todo Remove that `return_id` in the next major release
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Check if an entry exist by url.",
     *     @OA\Parameter(
     *         name="return_id",
     *         in="query",
     *         description="Set 1 if you want to retrieve ID in case entry(ies) exists, 0 by default",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="DEPRECATED, use hashed_url instead. An url",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="urls",
     *         in="query",
     *         description="DEPRECATED, use hashed_urls instead. An array of urls (?urls[]=http...&urls[]=http...)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="hashed_url",
     *         in="query",
     *         description="Hashed url using SHA1 to check if it exists. A hashed url",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="hashed_urls",
     *         in="query",
     *         description="An array of hashed urls using SHA1 to check if they exist. An array of hashed urls (?hashed_urls[]=xxx...&hashed_urls[]=xxx...)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/exists.{_format}", methods={"GET"}, name="api_get_entries_exists", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function getEntriesExistsAction(Request $request, EntryRepository $entryRepository)
    {
        $this->validateAuthentication();

        $returnId = (null === $request->query->get('return_id')) ? false : (bool) $request->query->get('return_id');

        $hashedUrls = $request->query->all('hashed_urls');
        $hashedUrl = $request->query->get('hashed_url', '');
        if (!empty($hashedUrl)) {
            $hashedUrls[] = $hashedUrl;
        }

        $urls = $request->query->all('urls');
        $url = $request->query->get('url', '');
        if (!empty($url)) {
            $urls[] = $url;
        }

        $urlHashMap = [];
        foreach ($urls as $urlToHash) {
            $urlHash = UrlHasher::hashUrl($urlToHash);
            $hashedUrls[] = $urlHash;
            $urlHashMap[$urlHash] = $urlToHash;
        }

        if (empty($hashedUrls)) {
            throw $this->createAccessDeniedException('URL is empty?, logged user id: ' . $this->getUser()->getId());
        }

        $results = array_fill_keys($hashedUrls, null);
        $res = $entryRepository->findByUserIdAndBatchHashedUrls($this->getUser()->getId(), $hashedUrls);
        foreach ($res as $e) {
            $_hashedUrl = array_keys($hashedUrls, 'blah', true);
            if ([] !== array_keys($hashedUrls, $e['hashedUrl'], true)) {
                $_hashedUrl = $e['hashedUrl'];
            } elseif ([] !== array_keys($hashedUrls, $e['hashedGivenUrl'], true)) {
                $_hashedUrl = $e['hashedGivenUrl'];
            } else {
                continue;
            }
            $results[$_hashedUrl] = $e['id'];
        }

        if (false === $returnId) {
            $results = array_map(function ($v) {
                return null !== $v;
            }, $results);
        }

        $results = $this->replaceUrlHashes($results, $urlHashMap);

        if (!empty($url) || !empty($hashedUrl)) {
            $hu = array_keys($results)[0];

            return $this->sendResponse(['exists' => $results[$hu]]);
        }

        return $this->sendResponse($results);
    }

    /**
     * Retrieve all entries. It could be filtered by many options.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Retrieve all entries. It could be filtered by many options.",
     *     @OA\Parameter(
     *         name="archive",
     *         in="query",
     *         description="filter by archived status. all entries by default.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="starred",
     *         in="query",
     *         description="filter by starred status. all entries by default",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notParsed",
     *         in="query",
     *         description="filter by notParsed status. all entries by default",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="sort entries by date.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"created", "updated", "archived"},
     *             default="created"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="order of sort.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"},
     *             default="desc"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="what page you want.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="results per page.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=30
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="a comma-seperated list of tags url encoded. Will returns entries that matches ALL tags.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="api,rest"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="since",
     *         in="query",
     *         description="The timestamp since when you want entries updated.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=0
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="public",
     *         in="query",
     *         description="filter by entries with a public link. all entries by default",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="detail",
     *         in="query",
     *         description="include content field if 'full'. 'full' by default for backward compatibility.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"metadata", "full"},
     *             default="full"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="domain_name",
     *         in="query",
     *         description="filter entries with the given domain name",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="example.com",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="http_status",
     *         in="query",
     *         description="filter entries with matching http status code",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             minimum=100,
     *             maximum=527,
     *             example="200",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries.{_format}", methods={"GET"}, name="api_get_entries", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function getEntriesAction(Request $request, EntryRepository $entryRepository)
    {
        $this->validateAuthentication();

        $isArchived = (null === $request->query->get('archive')) ? null : (bool) $request->query->get('archive');
        $isStarred = (null === $request->query->get('starred')) ? null : (bool) $request->query->get('starred');
        $isPublic = (null === $request->query->get('public')) ? null : (bool) $request->query->get('public');
        $isNotParsed = (null === $request->query->get('notParsed')) ? null : (bool) $request->query->get('notParsed');
        $sort = strtolower($request->query->get('sort', 'created'));
        $order = strtolower($request->query->get('order', 'desc'));
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 30);
        $tags = \is_array($request->query->get('tags')) ? '' : (string) $request->query->get('tags', '');
        $since = $request->query->get('since', 0);
        $detail = strtolower($request->query->get('detail', 'full'));
        $domainName = (null === $request->query->get('domain_name')) ? '' : (string) $request->query->get('domain_name');
        $httpStatus = (!\array_key_exists((int) $request->query->get('http_status'), Response::$statusTexts)) ? null : (int) $request->query->get('http_status');

        try {
            /** @var Pagerfanta $pager */
            $pager = $entryRepository->findEntries(
                $this->getUser()->getId(),
                $isArchived,
                $isStarred,
                $isPublic,
                $sort,
                $order,
                $since,
                $tags,
                $detail,
                $domainName,
                $isNotParsed,
                $httpStatus
            );
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $pager->setMaxPerPage($perPage);
        $pager->setCurrentPage($page);

        $pagerfantaFactory = new PagerfantaFactory('page', 'perPage');
        $paginatedCollection = $pagerfantaFactory->createRepresentation(
            $pager,
            new HateoasRoute(
                'api_get_entries',
                [
                    'archive' => $isArchived,
                    'starred' => $isStarred,
                    'public' => $isPublic,
                    'notParsed' => $isNotParsed,
                    'sort' => $sort,
                    'order' => $order,
                    'page' => $page,
                    'perPage' => $perPage,
                    'tags' => $tags,
                    'since' => $since,
                    'detail' => $detail,
                ],
                true
            )
        );

        return $this->sendResponse($paginatedCollection);
    }

    /**
     * Retrieve a single entry.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Retrieve a single entry.",
     *     @OA\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/{entry}.{_format}", methods={"GET"}, name="api_get_entry", defaults={"_format": "json"})
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
     * @Operation(
     *     tags={"Entries"},
     *     summary="Retrieve a single entry as a predefined format.",
     *     @OA\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="_format",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"xml", "json", "txt", "csv", "pdf", "epub"},
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/{entry}/export.{_format}", methods={"GET"}, name="api_get_entry_export", defaults={"_format": "json"})
     *
     * @return Response
     */
    public function getEntryExportAction(Entry $entry, Request $request, EntriesExport $entriesExport)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        return $entriesExport
            ->setEntries($entry)
            ->updateTitle('entry')
            ->updateAuthor('entry')
            ->exportAs($request->attributes->get('_format'));
    }

    /**
     * Handles an entries list and delete URL.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Handles an entries list and delete URL.",
     *     @OA\Parameter(
     *         name="urls",
     *         in="query",
     *         description="Urls (as an array) to delete. A JSON array of urls [{'url': 'http://...'}, {'url': 'http://...'}]",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/list.{_format}", methods={"DELETE"}, name="api_delete_entries_list", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function deleteEntriesListAction(Request $request, EntryRepository $entryRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->validateAuthentication();

        $urls = json_decode($request->query->get('urls', []));

        if (empty($urls)) {
            return $this->sendResponse([]);
        }

        $results = [];

        // handle multiple urls
        foreach ($urls as $key => $url) {
            $entry = $entryRepository->findByUrlAndUserId(
                $url,
                $this->getUser()->getId()
            );

            $results[$key]['url'] = $url;

            if (false !== $entry) {
                // entry deleted, dispatch event about it!
                $eventDispatcher->dispatch(new EntryDeletedEvent($entry), EntryDeletedEvent::NAME);

                $this->entityManager->remove($entry);
                $this->entityManager->flush();
            }

            $results[$key]['entry'] = $entry instanceof Entry ? true : false;
        }

        return $this->sendResponse($results);
    }

    /**
     * Handles an entries list and create URL.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Handles an entries list and create URL.",
     *     @OA\Parameter(
     *         name="urls",
     *         in="query",
     *         description="Urls (as an array) to create. A JSON array of urls [{'url': 'http://...'}, {'url': 'http://...'}]",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/lists.{_format}", methods={"POST"}, name="api_post_entries_list", defaults={"_format": "json"})
     *
     * @throws HttpException When limit is reached
     *
     * @return JsonResponse
     */
    public function postEntriesListAction(Request $request, EntryRepository $entryRepository, EventDispatcherInterface $eventDispatcher, ContentProxy $contentProxy)
    {
        $this->validateAuthentication();

        $urls = json_decode($request->query->get('urls', []));

        $limit = $this->getParameter('wallabag.api_limit_mass_actions');

        if (\count($urls) > $limit) {
            throw new BadRequestHttpException('API limit reached');
        }

        $results = [];
        if (empty($urls)) {
            return $this->sendResponse($results);
        }

        // handle multiple urls
        foreach ($urls as $key => $url) {
            $entry = $entryRepository->findByUrlAndUserId(
                $url,
                $this->getUser()->getId()
            );

            $results[$key]['url'] = $url;

            if (false === $entry) {
                $entry = new Entry($this->getUser());

                $contentProxy->updateEntry($entry, $url);
            }

            $this->entityManager->persist($entry);
            $this->entityManager->flush();

            $results[$key]['entry'] = $entry instanceof Entry ? $entry->getId() : false;

            // entry saved, dispatch event about it!
            $eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);
        }

        return $this->sendResponse($results);
    }

    /**
     * Create an entry.
     *
     * If you want to provide the HTML content (which means wallabag won't fetch it from the url), you must provide `content`, `title` & `url` fields **non-empty**.
     * Otherwise, content will be fetched as normal from the url and values will be overwritten.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Create an entry.",
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="Url for the entry.",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="http://www.test.com/article.html"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Optional, we'll get the title from the page.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="a comma-separated list of tags.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="tag1,tag2,tag3"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="archive",
     *         in="query",
     *         description="entry already archived",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="starred",
     *         in="query",
     *         description="entry already starred",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="Content of the entry",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Language of the entry",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="preview_picture",
     *         in="query",
     *         description="Preview picture of the entry",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="published_at",
     *         in="query",
     *         description="Published date of the entry",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="string",
     *             format="YYYY-MM-DDTHH:II:SS+TZ or a timestamp (integer)",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="authors",
     *         in="query",
     *         description="Authors of the entry",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="Name Firstname,author2,author3"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="public",
     *         in="query",
     *         description="will generate a public link for the entry",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="origin_url",
     *         in="query",
     *         description="Origin url for the entry (from where you found it).",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="http://www.test.com/article.html"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries.{_format}", methods={"POST"}, name="api_post_entries", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function postEntriesAction(
        Request $request,
        EntryRepository $entryRepository,
        ContentProxy $contentProxy,
        LoggerInterface $logger,
        TagsAssigner $tagsAssigner,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator
    ) {
        $this->validateAuthentication();

        $url = $request->request->get('url');

        $entry = $entryRepository->findByUrlAndUserId(
            $url,
            $this->getUser()->getId()
        );

        if (false === $entry) {
            $entry = new Entry($this->getUser());
            $entry->setUrl($url);
        }

        $data = $this->retrieveValueFromRequest($request);

        try {
            $contentProxy->updateEntry(
                $entry,
                $entry->getUrl(),
                [
                    'title' => !empty($data['title']) ? $data['title'] : $entry->getTitle(),
                    'html' => !empty($data['content']) ? $data['content'] : $entry->getContent(),
                    'url' => $entry->getUrl(),
                    'language' => !empty($data['language']) ? $data['language'] : $entry->getLanguage(),
                    'date' => !empty($data['publishedAt']) ? $data['publishedAt'] : $entry->getPublishedAt(),
                    // faking the open graph preview picture
                    'image' => !empty($data['picture']) ? $data['picture'] : $entry->getPreviewPicture(),
                    'authors' => \is_string($data['authors']) ? explode(',', $data['authors']) : $entry->getPublishedBy(),
                ]
            );
        } catch (\Exception $e) {
            $logger->error('Error while saving an entry', [
                'exception' => $e,
                'entry' => $entry,
            ]);
        }

        if (null !== $data['isArchived']) {
            $entry->updateArchived((bool) $data['isArchived']);
        }

        if (null !== $data['isStarred']) {
            $entry->updateStar((bool) $data['isStarred']);
        }

        if (!empty($data['tags'])) {
            $tagsAssigner->assignTagsToEntry($entry, $data['tags']);
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
            $contentProxy->setEntryDomainName($entry);
        }

        if (empty($entry->getTitle())) {
            $contentProxy->setDefaultEntryTitle($entry);
        }

        $errors = $validator->validate($entry);
        if (\count($errors) > 0) {
            $errorsString = (string) $errors;

            return $this->sendResponse($errorsString);
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        // entry saved, dispatch event about it!
        $eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);

        return $this->sendResponse($entry);
    }

    /**
     * Change several properties of an entry.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Change several properties of an entry.",
     *     @OA\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="a comma-separated list of tags.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="tag1,tag2,tag3",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="archive",
     *         in="query",
     *         description="archived the entry.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="starred",
     *         in="query",
     *         description="starred the entry.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="Content of the entry",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Language of the entry",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="preview_picture",
     *         in="query",
     *         description="Preview picture of the entry",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="published_at",
     *         in="query",
     *         description="Published date of the entry",
     *         required=false,
     *         @OA\Schema(
     *             type="datetime|integer",
     *             format="YYYY-MM-DDTHH:II:SS+TZ or a timestamp",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="authors",
     *         in="query",
     *         description="Authors of the entry",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="Name Firstname,author2,author3",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="public",
     *         in="query",
     *         description="will generate a public link for the entry",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={"1", "0"},
     *             default="0"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="origin_url",
     *         in="query",
     *         description="Origin url for the entry (from where you found it).",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="http://www.test.com/article.html",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/{entry}.{_format}", methods={"PATCH"}, name="api_patch_entries", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function patchEntriesAction(Entry $entry, Request $request, ContentProxy $contentProxy, LoggerInterface $logger, TagsAssigner $tagsAssigner, EventDispatcherInterface $eventDispatcher)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

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
                $logger->error('Error while saving an entry', [
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

        if (!empty($data['authors']) && \is_string($data['authors'])) {
            $entry->setPublishedBy(explode(',', $data['authors']));
        }

        if (!empty($data['picture'])) {
            $contentProxy->updatePreviewPicture($entry, $data['picture']);
        }

        if (!empty($data['publishedAt'])) {
            $contentProxy->updatePublishedAt($entry, $data['publishedAt']);
        }

        if (null !== $data['isArchived']) {
            $entry->updateArchived((bool) $data['isArchived']);
        }

        if (null !== $data['isStarred']) {
            $entry->updateStar((bool) $data['isStarred']);
        }

        if (!empty($data['tags'])) {
            $entry->removeAllTags();
            $tagsAssigner->assignTagsToEntry($entry, $data['tags']);
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
            $contentProxy->setEntryDomainName($entry);
        }

        if (empty($entry->getTitle())) {
            $contentProxy->setDefaultEntryTitle($entry);
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        // entry saved, dispatch event about it!
        $eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);

        return $this->sendResponse($entry);
    }

    /**
     * Reload an entry.
     * An empty response with HTTP Status 304 will be send if we weren't able to update the content (because it hasn't changed or we got an error).
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Reload an entry.",
     *     @OA\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/{entry}/reload.{_format}", methods={"PATCH"}, name="api_patch_entries_reload", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function patchEntriesReloadAction(Entry $entry, ContentProxy $contentProxy, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        try {
            $contentProxy->updateEntry($entry, $entry->getUrl());
        } catch (\Exception $e) {
            $logger->error('Error while saving an entry', [
                'exception' => $e,
                'entry' => $entry,
            ]);

            return new JsonResponse([], 304);
        }

        // if refreshing entry failed, don't save it
        if ($this->getParameter('wallabag.fetching_error_message') === $entry->getContent()) {
            return new JsonResponse([], 304);
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        // entry saved, dispatch event about it!
        $eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);

        return $this->sendResponse($entry);
    }

    /**
     * Delete **permanently** an entry.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Delete permanently an entry.",
     *     @OA\Parameter(
     *         name="expect",
     *         in="query",
     *         description="Only returns the id instead of the deleted entry's full entity if 'id' is specified.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"id", "entry"},
     *             default="entry"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/{entry}.{_format}", methods={"DELETE"}, name="api_delete_entries", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function deleteEntriesAction(Entry $entry, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $expect = $request->query->get('expect', 'entry');
        if (!\in_array($expect, ['id', 'entry'], true)) {
            throw new BadRequestHttpException(\sprintf("expect: 'id' or 'entry' expected, %s given", $expect));
        }
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $response = $this->sendResponse([
            'id' => $entry->getId(),
        ]);
        // We clone $entry to keep id in returned object
        if ('entry' === $expect) {
            $e = clone $entry;
            $response = $this->sendResponse($e);
        }

        // entry deleted, dispatch event about it!
        $eventDispatcher->dispatch(new EntryDeletedEvent($entry), EntryDeletedEvent::NAME);

        $this->entityManager->remove($entry);
        $this->entityManager->flush();

        return $response;
    }

    /**
     * Retrieve all tags for an entry.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Retrieve all tags for an entry.",
     *     @OA\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/{entry}/tags.{_format}", methods={"GET"}, name="api_get_entries_tags", defaults={"_format": "json"})
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
     * @Operation(
     *     tags={"Entries"},
     *     summary="Add one or more tags to an entry.",
     *     @OA\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="a comma-separated list of tags.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="tag1,tag2,tag3",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/{entry}/tags.{_format}", methods={"POST"}, name="api_post_entries_tags", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function postEntriesTagsAction(Request $request, Entry $entry, TagsAssigner $tagsAssigner)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $tags = $request->request->get('tags', '');
        if (!empty($tags)) {
            $tagsAssigner->assignTagsToEntry($entry, $tags);
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        return $this->sendResponse($entry);
    }

    /**
     * Permanently remove one tag for an entry.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Permanently remove one tag for an entry.",
     *     @OA\Parameter(
     *         name="entry",
     *         in="path",
     *         description="The entry ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         description="The tag ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             pattern="\w+",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/{entry}/tags/{tag}.{_format}", methods={"DELETE"}, name="api_delete_entries_tags", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function deleteEntriesTagsAction(Entry $entry, Tag $tag)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $entry->removeTag($tag);

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        return $this->sendResponse($entry);
    }

    /**
     * Handles an entries list delete tags from them.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Handles an entries list delete tags from them.",
     *     @OA\Parameter(
     *         name="list",
     *         in="query",
     *         description="Urls (as an array) to handle. A JSON array of urls [{'url': 'http://...','tags': 'tag1, tag2'}, {'url': 'http://...','tags': 'tag1, tag2'}]",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/tags/list.{_format}", methods={"DELETE"}, name="api_delete_entries_tags_list", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function deleteEntriesTagsListAction(Request $request, TagRepository $tagRepository, EntryRepository $entryRepository)
    {
        $this->validateAuthentication();

        $list = json_decode($request->query->get('list', []));

        if (empty($list)) {
            return $this->sendResponse([]);
        }

        // handle multiple urls
        $results = [];

        foreach ($list as $key => $element) {
            $entry = $entryRepository->findByUrlAndUserId(
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

                    $tag = $tagRepository->findOneByLabel($label);

                    if (false !== $tag) {
                        $entry->removeTag($tag);
                    }
                }

                $this->entityManager->persist($entry);
                $this->entityManager->flush();
            }
        }

        return $this->sendResponse($results);
    }

    /**
     * Handles an entries list and add tags to them.
     *
     * @Operation(
     *     tags={"Entries"},
     *     summary="Handles an entries list and add tags to them.",
     *     @OA\Parameter(
     *         name="list",
     *         in="query",
     *         description="Urls (as an array) to handle. A JSON array of urls [{'url': 'http://...','tags': 'tag1, tag2'}, {'url': 'http://...','tags': 'tag1, tag2'}]",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/entries/tags/lists.{_format}", methods={"POST"}, name="api_post_entries_tags_list", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function postEntriesTagsListAction(Request $request, EntryRepository $entryRepository, TagsAssigner $tagsAssigner)
    {
        $this->validateAuthentication();

        $list = json_decode($request->query->get('list', []));

        if (empty($list)) {
            return $this->sendResponse([]);
        }

        $results = [];

        // handle multiple urls
        foreach ($list as $key => $element) {
            $entry = $entryRepository->findByUrlAndUserId(
                $element->url,
                $this->getUser()->getId()
            );

            $results[$key]['url'] = $element->url;
            $results[$key]['entry'] = $entry instanceof Entry ? $entry->getId() : false;

            $tags = $element->tags;

            if (false !== $entry && !(empty($tags))) {
                $tagsAssigner->assignTagsToEntry($entry, $tags);

                $this->entityManager->persist($entry);
                $this->entityManager->flush();
            }
        }

        return $this->sendResponse($results);
    }

    /**
     * Replace the hashedUrl keys in $results with the unhashed URL from the
     * request, as recorded in $urlHashMap.
     */
    private function replaceUrlHashes(array $results, array $urlHashMap)
    {
        $newResults = [];
        foreach ($results as $hash => $res) {
            if (isset($urlHashMap[$hash])) {
                $newResults[$urlHashMap[$hash]] = $res;
            } else {
                $newResults[$hash] = $res;
            }
        }

        return $newResults;
    }

    /**
     * Retrieve value from the request.
     * Used for POST & PATCH on a an entry.
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
}
