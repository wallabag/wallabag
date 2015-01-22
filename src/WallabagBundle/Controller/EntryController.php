<?php

namespace WallabagBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WallabagBundle\Repository;

class EntryController extends Controller
{
    /**
     * @Route("/unread", name="unread")
     */
    public function unreadAction()
    {
        $repository = $this->getDoctrine()->getRepository('WallabagBundle:Entries');
        $entries = $repository->findUnreadByUser(1);

        return $this->render(
            'WallabagBundle:Entry:entries.html.twig',
            array('entries' => $entries)
        );

    }
}
