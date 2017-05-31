<?php

namespace Wallabag\FederationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Event\Activity\Actions\Federation\RecommendedEntryEvent;
use Wallabag\FederationBundle\Entity\Account;

class RecommandController extends Controller
{
    /**
     * @Route("/recommend/{entry}", name="recommend-entry")
     *
     * @param Entry $entry
     */
    public function postRecommendAction(Entry $entry)
    {
        if ($entry->getUser() !== $this->getUser()) {
            $this->createAccessDeniedException("You can't recommend entries which are not your own");
        }
        $em = $this->getDoctrine()->getManager();

        $entry->setRecommended(true);

        $em->persist($entry);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(RecommendedEntryEvent::NAME, new RecommendedEntryEvent($entry));

        $this->redirectToRoute('view', ['id' => $entry->getId()]);
    }
}
