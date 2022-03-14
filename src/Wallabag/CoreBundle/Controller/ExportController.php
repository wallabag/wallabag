<?php

namespace Wallabag\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * The try/catch can be removed once all formats will be implemented.
 * Still need implementation: txt.
 */
class ExportController extends Controller
{
    /**
     * Gets one entry content.
     *
     * @param string $format
     *
     * @Route("/export/{id}.{format}", name="export_entry", requirements={
     *     "format": "epub|mobi|pdf|json|xml|txt|csv",
     *     "id": "\d+"
     * })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadEntryAction(Entry $entry, $format)
    {
        try {
            return $this->get('wallabag_core.helper.entries_export')
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
     * @param string $format
     * @param string $category
     *
     * @Route("/export/{category}.{format}", name="export_entries", requirements={
     *     "format": "epub|mobi|pdf|json|xml|txt|csv",
     *     "category": "all|unread|starred|archive|tag_entries|untagged|search|annotated|same_domain"
     * })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadEntriesAction(Request $request, $format, $category)
    {
        $method = ucfirst($category);
        $methodBuilder = 'getBuilderFor' . $method . 'ByUser';
        $repository = $this->get('wallabag_core.entry_repository');
        $title = $method;

        if ('tag_entries' === $category) {
            $tag = $this->get('wallabag_core.tag_repository')->findOneBySlug($request->query->get('tag'));

            $entries = $repository->findAllByTagId(
                $this->getUser()->getId(),
                $tag->getId()
            );

            $title = 'Tag ' . $tag->getLabel();
        } elseif ('search' === $category) {
            $searchTerm = (isset($request->get('search_entry')['term']) ? $request->get('search_entry')['term'] : '');
            $currentRoute = (null !== $request->query->get('currentRoute') ? $request->query->get('currentRoute') : '');

            $entries = $repository->getBuilderForSearchByUser(
                    $this->getUser()->getId(),
                    $searchTerm,
                    $currentRoute
            )->getQuery()
             ->getResult();

            $title = 'Search ' . $searchTerm;
        } elseif ('annotated' === $category) {
            $entries = $repository->getBuilderForAnnotationsByUser(
                $this->getUser()->getId()
            )->getQuery()
             ->getResult();

            $title = 'With annotations';
        } else {
            $entries = $repository
                ->$methodBuilder($this->getUser()->getId())
                ->getQuery()
                ->getResult();
        }

        try {
            return $this->get('wallabag_core.helper.entries_export')
                ->setEntries($entries)
                ->updateTitle($title)
                ->updateAuthor($method)
                ->exportAs($format);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
