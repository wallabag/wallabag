<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     *     "category": "all|unread|starred|archive|tag_entries|untagged"
     * })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadEntriesAction($format, $category)
    {
        $method = ucfirst($category);
        $methodBuilder = 'getBuilderFor'.$method.'ByUser';
        $entries = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->$methodBuilder($this->getUser()->getId())
            ->getQuery()
            ->getResult();

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
