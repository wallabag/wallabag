<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Helper\EntriesExport;

class ExportController extends Controller
{
    /**
     * Gets all entries for current user.
     *
     * @Route("/export/{category}.{format}", name="ebook", requirements={
     *     "_format": "epub|mobi|pdf|json|xml|txt|csv"
     * })
     */
    public function getEntriesAction($format, $category)
    {
        $repository = $this->getDoctrine()->getRepository('WallabagCoreBundle:Entry');
        switch ($category) {
            case 'all':
                $method = 'All';
                break;

            case 'unread':
                $method = 'Unread';
                break;

            case 'starred':
                $method = 'Starred';
                break;

            case 'archive':
                $method = 'Archive';
                break;

            default:
                break;
        }

        $methodBuilder = 'getBuilderFor'.$method.'ByUser';
        $qb = $repository->$methodBuilder($this->getUser()->getId());
        $entries = $qb->getQuery()->getResult();

        $export = new EntriesExport($entries);
        $export->setMethod($method);
        $export->exportAs($format);
    }

    /**
     * Gets one entry content.
     *
     * @param Entry $entry
     *
     * @Route("/export/id/{id}.{format}", requirements={"id" = "\d+"}, name="ebook_entry")
     */
    public function getEntryAction(Entry $entry, $format)
    {
        $export = new EntriesExport(array($entry));
        $export->setMethod('entry');
        $export->exportAs($format);
    }
}
