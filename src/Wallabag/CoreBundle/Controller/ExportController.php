<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;

/**
 * The try/catch can be removed once all formats will be implemented.
 * Still need implementation: txt.
 */
class ExportController extends Controller
{
    /**
     * Gets one entry content.
     *
     * @param Entry  $entry
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
     *     "category": "all|unread|starred|archive|tag_entries|untagged|search"
     * })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadEntriesAction(Request $request, $format, $category)
    {
        $method = ucfirst($category);
        $methodBuilder = 'getBuilderFor'.$method.'ByUser';

        if ($category == 'tag_entries') {
            $tag = $this->getDoctrine()->getRepository('WallabagCoreBundle:Tag')->findOneBySlug($request->query->get('tag'));

            $entries = $this->getDoctrine()
                ->getRepository('WallabagCoreBundle:Entry')
                ->findAllByTagId($this->getUser()->getId(), $tag->getId());
        } else {
            $entries = $this->getDoctrine()
                ->getRepository('WallabagCoreBundle:Entry')
                ->$methodBuilder($this->getUser()->getId())
                ->getQuery()
                ->getResult();
        }

        try {
            return $this->get('wallabag_core.helper.entries_export')
                ->setEntries($entries)
                ->updateTitle($method)
                ->exportAs($format);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
