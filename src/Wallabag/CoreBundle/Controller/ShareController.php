<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Notification;
use Wallabag\CoreBundle\Entity\Share;
use Wallabag\CoreBundle\Event\Activity\Actions\Entry\EntrySavedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Share\ShareAcceptedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Share\ShareCancelledEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Share\ShareCreatedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Share\ShareDeniedEvent;
use Wallabag\CoreBundle\Notifications\NoAction;
use Wallabag\CoreBundle\Notifications\YesAction;
use Wallabag\UserBundle\Entity\User;

class ShareController extends Controller
{
    /**
     * @Route("/share-user/{entry}/{destination}", name="share-entry-user", requirements={"entry" = "\d+", "destination" = "\d+"})
     * @param Entry $entry
     * @param User $destination
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     */
    public function shareEntryAction(Entry $entry, User $destination)
    {

        if ($entry->getUser() !== $this->getUser()) {
            throw new AccessDeniedException("You can't share this entry");
        }

        if ($destination === $this->getUser()) {
            throw new InvalidArgumentException("You can't share entries to yourself");
        }

        $share = new Share();
        $share->setUserOrigin($this->getUser())
            ->setEntry($entry)
            ->setUserDestination($destination);

        $em = $this->getDoctrine()->getManager();
        $em->persist($share);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(ShareCreatedEvent::NAME, new ShareCancelledEvent($share));

        $accept = new YesAction($this->generateUrl('share-entry-user-accept', ['share' => $share->getId()]));

        $deny = new NoAction($this->generateUrl('share-entry-user-refuse', ['share' => $share->getId()]));

        $notification = new Notification($destination);
        $notification->setType(Notification::TYPE_SHARE)
            ->setTitle($this->get('translator')->trans('share.notification.new.title'))
            ->addAction($accept)
            ->addAction($deny);

        $em->persist($notification);
        $em->flush();

        $this->redirectToRoute('view', ['id' => $entry->getId()]);
    }

    /**
     * @Route("/share-user/accept/{share}", name="share-entry-user-accept")
     *
     * @param Share $share
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function acceptShareAction(Share $share)
    {
        if ($share->getUserDestination() !== $this->getUser()) {
            throw new AccessDeniedException("You can't accept this entry");
        }

        $entry = new Entry($this->getUser());
        $entry->setUrl($share->getEntry()->getUrl());

        $em = $this->getDoctrine()->getManager();

        if (false === $this->checkIfEntryAlreadyExists($entry)) {
            $this->updateEntry($entry);

            $em->persist($entry);
            $em->flush();

            $this->get('event_dispatcher')->dispatch(ShareAcceptedEvent::NAME, new ShareAcceptedEvent($share));

            // entry saved, dispatch event about it!
            $this->get('event_dispatcher')->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));
        }

        $em->remove($share);
        $em->flush(); // we keep the previous flush above in case the event dispatcher would lead in using the saved entry

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @Route("/share-user/refuse/{share}", name="share-entry-user-refuse")
     *
     * @param Share $share
     * @return RedirectResponse
     */
    public function refuseShareAction(Share $share)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($share);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(ShareDeniedEvent::NAME, new ShareDeniedEvent($share));

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Fetch content and update entry.
     * In case it fails, entry will return to avod loosing the data.
     *
     * @param Entry  $entry
     * @param string $prefixMessage Should be the translation key: entry_saved or entry_reloaded
     *
     * @return Entry
     */
    private function updateEntry(Entry $entry, $prefixMessage = 'entry_saved')
    {
        // put default title in case of fetching content failed
        $entry->setTitle('No title found');

        $message = 'flashes.entry.notice.'.$prefixMessage;

        try {
            $entry = $this->get('wallabag_core.content_proxy')->updateEntry($entry, $entry->getUrl());
        } catch (\Exception $e) {
            $this->get('logger')->error('Error while saving an entry', [
                'exception' => $e,
                'entry' => $entry,
            ]);

            $message = 'flashes.entry.notice.'.$prefixMessage.'_failed';
        }

        $this->get('session')->getFlashBag()->add('notice', $message);

        return $entry;
    }

    /**
     * Check for existing entry, if it exists, redirect to it with a message.
     *
     * @param Entry $entry
     *
     * @return Entry|bool
     */
    private function checkIfEntryAlreadyExists(Entry $entry)
    {
        return $this->get('wallabag_core.entry_repository')->findByUrlAndUserId($entry->getUrl(), $this->getUser()->getId());
    }
}
