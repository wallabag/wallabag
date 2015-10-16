<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wallabag\CoreBundle\Entity\Entry;

class ExportController extends Controller
{
    /**
     * Gets one entry content.
     *
     * @param Entry $entry
     *
     * @Route("/export/{id}.{format}", requirements={"id" = "\d+"}, name="export_entry")
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
     * @Route("/export/{category}.{format}", name="export_entries", requirements={
     *     "_format": "epub|mobi|pdf|json|xml|txt|csv",
     *     "category": "all|unread|starred|archive"
     * })
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
