<?php

namespace Wallabag\CoreBundle\Event\Activity;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wallabag\CoreBundle\Entity\Activity;
use Wallabag\CoreBundle\Event\Activity\Actions\Annotation\AnnotationCreatedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Annotation\AnnotationDeletedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Annotation\AnnotationEditedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Annotation\AnnotationEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Entry\EntryDeletedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Entry\EntryEditedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Entry\EntryEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Entry\EntryFavouriteEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Entry\EntryReadEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Entry\EntrySavedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Entry\EntryTaggedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Federation\FollowEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Federation\RecommendedEntryEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Federation\UnfollowEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Share\ShareAcceptedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Share\ShareCancelledEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Share\ShareCreatedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Share\ShareDeniedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\Share\ShareEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\User\UserDeletedEvent;
use Wallabag\CoreBundle\Event\Activity\Actions\User\UserEditedEvent;
use Wallabag\CoreBundle\Notifications\ActionInterface;

/**
 * This listener will create the associated configuration when a user register.
 * This configuration will be created right after the registration (no matter if it needs an email validation).
 */
class ActivitySubscriber implements EventSubscriberInterface
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            EntrySavedEvent::NAME => 'entryActivity',
            EntryDeletedEvent::NAME => 'entryActivity',
            EntryEditedEvent::NAME => 'entryActivity',
            EntryTaggedEvent::NAME => 'taggedEntry',
            EntryFavouriteEvent::NAME => 'entryActivity',
            EntryReadEvent::NAME => 'entryActivity',

            AnnotationCreatedEvent::NAME => 'annotationActivity',
            AnnotationEditedEvent::NAME => 'annotationActivity',
            AnnotationDeletedEvent::NAME => 'annotationActivity',

            FollowEvent::NAME => 'followedAccount',
            UnfollowEvent::NAME => 'unfollowedAccount',
            RecommendedEntryEvent::NAME => 'recommendedEntry',

            ShareCreatedEvent::NAME => 'shareActivity',
            ShareAcceptedEvent::NAME => 'shareActivity',
            ShareDeniedEvent::NAME => 'shareActivity',
            ShareCancelledEvent::NAME => 'shareActivity',

            // when a user register using the normal form
            FOSUserEvents::REGISTRATION_COMPLETED => 'userActivity',
            // when we manually create a user using the command line
            // OR when we create it from the config UI
            FOSUserEvents::USER_CREATED => 'userActivity',
            UserEditedEvent::NAME => 'userActivity',
            UserDeletedEvent::NAME => 'userActivity',
        ];
    }

    public function userActivity(Event $event)
    {
        $activityType = 0;
        if ($event instanceof UserEvent) {
            $activityType = Activity::USER_CREATE;
        } elseif ($event instanceof UserEditedEvent) {
            $activityType = Activity::USER_EDIT;
        } elseif ($event instanceof UserDeletedEvent) {
            $activityType = Activity::USER_REMOVE;
        }

        $user = $event->getUser();
        $activity = new Activity($activityType, Activity::USER_OBJECT, $user->getId());
        $activity->setUser($user->getAccount());
        $this->em->persist($activity);
        $this->em->flush();
    }

    public function entryActivity(EntryEvent $event)
    {
        $entry = $event->getEntry();

        $activityType = 0;
        if ($event instanceof EntrySavedEvent) {
            $activityType = Activity::ENTRY_ADD;
        } elseif ($event instanceof EntryDeletedEvent) {
            $activityType = Activity::ENTRY_DELETE;
        } elseif ($event instanceof EntryEditedEvent) {
            $activityType = Activity::ENTRY_EDIT;
        } elseif ($event instanceof EntryFavouriteEvent) {
            if ($entry->isStarred()) {
                $activityType = Activity::ENTRY_FAVOURITE;
            } else {
                $activityType = Activity::ENTRY_UNFAVOURITE;
            }
        } elseif ($event instanceof EntryReadEvent) {
            if ($entry->isArchived()) {
                $activityType = Activity::ENTRY_READ;
            } else {
                $activityType = Activity::ENTRY_UNREAD;
            }
        }

        $activity = new Activity($activityType, Activity::ENTRY_OBJECT, $entry->getId());
        $activity->setUser($entry->getUser()->getAccount());
        $this->em->persist($activity);
        $this->em->flush();
    }

    public function taggedEntry(EntryTaggedEvent $event)
    {
        $entry = $event->getEntry();
        $activity = new Activity($event->isRemove() ? Activity::ENTRY_REMOVE_TAG : Activity::ENTRY_ADD_TAG, Activity::ENTRY_OBJECT, $entry->getId());
        $activity->setUser($entry->getUser()->getAccount());
        $activity->setSecondaryObjectType(Activity::TAG_OBJECT)
            ->setSecondaryObjectId($event->getTags()[0]->getId());
        $this->em->persist($activity);
        $this->em->flush();
    }

    public function annotationActivity(AnnotationEvent $event)
    {
        $annotation = $event->getAnnotation();

        $activityType = 0;
        if ($event instanceof AnnotationCreatedEvent) {
            $activityType = Activity::ANNOTATION_ADD;
        } elseif ($event instanceof AnnotationEditedEvent) {
            $activityType = Activity::ANNOTATION_EDIT;
        } elseif ($event instanceof AnnotationDeletedEvent) {
            $activityType = Activity::ANNOTATION_REMOVE;
        }

        $activity = new Activity($activityType, Activity::ANNOTATION_OBJECT, $annotation->getId());
        $activity->setUser($annotation->getUser()->getAccount());
        $this->em->persist($activity);
        $this->em->flush();
    }

    public function followedAccount(FollowEvent $event)
    {
        $activity = new Activity(Activity::FOLLOW_ACCOUNT, Activity::ACCOUNT_OBJECT, $event->getAccount()->getId());
        $activity->setUser($event->getAccount());
        $activity->setSecondaryObjectType(Activity::ACCOUNT_OBJECT)
            ->setSecondaryObjectId($event->getFollower()->getId());
        $this->em->persist($activity);
        $this->em->flush();
    }

    public function unfollowedAccount(UnfollowEvent $event)
    {
        $activity = new Activity(Activity::UNFOLLOW_ACCOUNT, Activity::ACCOUNT_OBJECT, $event->getAccount()->getId());
        $activity->setUser($event->getAccount());
        $activity->setSecondaryObjectType(Activity::ACCOUNT_OBJECT)
            ->setSecondaryObjectId($event->getFollower()->getId());
        $this->em->persist($activity);
        $this->em->flush();
    }

    public function recommendedEntry(RecommendedEntryEvent $event)
    {
        $entry = $event->getEntry();
        $account = $entry->getUser()->getAccount();
        $activity = new Activity(Activity::RECOMMEND_ENTRY, Activity::ACCOUNT_OBJECT, $account->getId());
        $activity->setUser($account);
        $activity->setSecondaryObjectType(Activity::ENTRY_OBJECT)
            ->setSecondaryObjectId($entry->getId());
        $this->em->persist($activity);
        $this->em->flush();
    }

    public function shareActivity(ShareEvent $event)
    {
        $share = $event->getShare();

        $activityType = 0;
        if ($event instanceof ShareCreatedEvent) {
            $activityType = Activity::USER_SHARE_CREATED;
        } elseif ($event instanceof ShareAcceptedEvent) {
            $activityType = Activity::USER_SHARE_ACCEPTED;
        } elseif ($event instanceof ShareDeniedEvent) {
            $activityType = Activity::USER_SHARE_REFUSED;
        } elseif ($event instanceof ShareCancelledEvent) {
            $activityType = Activity::USER_SHARE_CANCELLED;
        }

        $activity = new Activity($activityType, Activity::SHARE_OBJECT, $share->getId());
        $activity->setUser($share->getUserOrigin());
        $activity->setSecondaryObjectType(Activity::ACCOUNT_OBJECT)
            ->setSecondaryObjectId($share->getUserDestination()->getId());
        $this->em->persist($activity);
        $this->em->flush();
    }
}
