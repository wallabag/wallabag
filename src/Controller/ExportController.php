<?php

namespace Wallabag\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Helper\EntriesExport;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\TagRepository;

/**
 * The try/catch can be removed once all formats will be implemented.
 * Still need implementation: txt.
 */
class ExportController extends AbstractController
{
    /**
     * Gets one entry content.
     *
     * @Route("/export/{id}.{format}", name="export_entry", requirements={
     *     "format": "epub|pdf|json|xml|txt|csv|md",
     *     "id": "\d+"
     * })
     *
     * @return Response
     */
    public function downloadEntryAction(Request $request, EntryRepository $entryRepository, EntriesExport $entriesExport, string $format, int $id)
    {
        try {
            $entry = $entryRepository->find($id);

            /*
             * We duplicate EntryController::checkUserAction here as a quick fix for an improper authorization vulnerability
             *
             * This should be eventually rewritten
             */
            if (null === $entry || null === $this->getUser() || $this->getUser()->getId() !== $entry->getUser()->getId()) {
                throw new NotFoundHttpException();
            }

            return $entriesExport
                ->setEntries($entry)
                ->updateTitle('entry')
                ->updateAuthor('entry')
                ->exportAs($format);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * Export all entries for current user.
     *
     * @Route("/export/{category}.{format}", name="export_entries", requirements={
     *     "format": "epub|pdf|json|xml|txt|csv|md",
     *     "category": "all|unread|starred|archive|tag_entries|untagged|search|annotated|same_domain"
     * })
     *
     * @return Response
     */
    public function downloadEntriesAction(Request $request, EntryRepository $entryRepository, TagRepository $tagRepository, EntriesExport $entriesExport, string $format, string $category, int $entry = 0)
    {
        $method = ucfirst($category);
        $methodBuilder = 'getBuilderFor' . $method . 'ByUser';
        $title = $method;

        if ('same_domain' === $category) {
            $entries = $entryRepository->getBuilderForSameDomainByUser(
                $this->getUser()->getId(),
                $request->query->get('entry')
            )->getQuery()
             ->getResult();

            $title = 'Same domain';
        } elseif ('tag_entries' === $category) {
            $tag = $tagRepository->findOneBySlug($request->query->get('tag'));

            $entries = $entryRepository->findAllByTagId(
                $this->getUser()->getId(),
                $tag->getId()
            );

            $title = 'Tag ' . $tag->getLabel();
        } elseif ('search' === $category) {
            $searchTerm = (isset($request->query->get('search_entry')['term']) ? $request->query->get('search_entry')['term'] : '');
            $currentRoute = (null !== $request->query->get('currentRoute') ? $request->query->get('currentRoute') : '');

            $entries = $entryRepository->getBuilderForSearchByUser(
                $this->getUser()->getId(),
                $searchTerm,
                $currentRoute
            )->getQuery()
             ->getResult();

            $title = 'Search ' . $searchTerm;
        } elseif ('annotated' === $category) {
            $entries = $entryRepository->getBuilderForAnnotationsByUser(
                $this->getUser()->getId()
            )->getQuery()
             ->getResult();

            $title = 'With annotations';
        } else {
            $entries = $entryRepository
                ->$methodBuilder($this->getUser()->getId())
                ->getQuery()
                ->getResult();
        }

        try {
            return $entriesExport
                ->setEntries($entries)
                ->updateTitle($title)
                ->updateAuthor($method)
                ->exportAs($format);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
