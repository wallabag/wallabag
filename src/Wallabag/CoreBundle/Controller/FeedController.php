<?php

namespace Wallabag\CoreBundle\Controller;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\UserBundle\Entity\User;

class FeedController extends Controller
{
    /**
     * Shows unread entries for current user.
     *
     * @Route("/feed/{username}/{token}/unread/{page}", name="unread_feed", defaults={"page"=1, "_format"="xml"})
     *
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_feed_token_converter")
     *
     * @param $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showUnreadFeedAction(User $user, $page)
    {
        return $this->showEntries('unread', $user, $page);
    }

    /**
     * Shows read entries for current user.
     *
     * @Route("/feed/{username}/{token}/archive/{page}", name="archive_feed", defaults={"page"=1, "_format"="xml"})
     *
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_feed_token_converter")
     *
     * @param $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArchiveFeedAction(User $user, $page)
    {
        return $this->showEntries('archive', $user, $page);
    }

    /**
     * Shows starred entries for current user.
     *
     * @Route("/feed/{username}/{token}/starred/{page}", name="starred_feed", defaults={"page"=1, "_format"="xml"})
     *
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_feed_token_converter")
     *
     * @param $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showStarredFeedAction(User $user, $page)
    {
        return $this->showEntries('starred', $user, $page);
    }

    /**
     * Shows all entries for current user.
     *
     * @Route("/feed/{username}/{token}/all/{page}", name="all_feed", defaults={"page"=1, "_format"="xml"})
     *
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_feed_token_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAllFeedAction(User $user, $page)
    {
        return $this->showEntries('all', $user, $page);
    }

    /**
     * Shows entries associated to a tag for current user.
     *
     * @Route("/feed/{username}/{token}/tags/{slug}/{page}", name="tag_feed", defaults={"page"=1, "_format"="xml"})
     *
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_feed_token_converter")
     * @ParamConverter("tag", options={"mapping": {"slug": "slug"}})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showTagsFeedAction(User $user, Tag $tag, $page)
    {
        $url = $this->generateUrl(
            'tag_feed',
            [
                'username' => $user->getUsername(),
                'token' => $user->getConfig()->getFeedToken(),
                'slug' => $tag->getSlug(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $entriesByTag = $this->get('wallabag_core.entry_repository')->findAllByTagId(
            $user->getId(),
            $tag->getId()
        );

        $pagerAdapter = new ArrayAdapter($entriesByTag);

        $entries = $this->get('wallabag_core.helper.prepare_pager_for_entries')->prepare(
            $pagerAdapter,
            $user
        );

        if (null === $entries) {
            throw $this->createNotFoundException('No entries found?');
        }

        try {
            $entries->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($url . '?page=' . $entries->getNbPages(), 302);
            }
        }

        return $this->render(
            '@WallabagCore/themes/common/Entry/entries.xml.twig',
            [
                'type' => 'tag',
                'url' => $url,
                'entries' => $entries,
                'user' => $user->getUsername(),
                'domainName' => $this->getParameter('domain_name'),
                'version' => $this->getParameter('wallabag_core.version'),
                'tag' => $tag->getSlug(),
                'isFeedUseSource' => $user->getConfig()->isFeedUseSource(),
            ],
            new Response('', 200, ['Content-Type' => 'application/atom+xml'])
        );
    }

    /**
     * Global method to retrieve entries depending on the given type
     * It returns the response to be send.
     *
     * @param string $type Entries type: unread, starred or archive
     * @param int    $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function showEntries($type, User $user, $page = 1)
    {
        $repository = $this->get('wallabag_core.entry_repository');

        switch ($type) {
            case 'starred':
                $qb = $repository->getBuilderForStarredByUser($user->getId());
                break;
            case 'archive':
                $qb = $repository->getBuilderForArchiveByUser($user->getId());
                break;
            case 'unread':
                $qb = $repository->getBuilderForUnreadByUser($user->getId());
                break;
            case 'all':
                $qb = $repository->getBuilderForAllByUser($user->getId());
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Type "%s" is not implemented.', $type));
        }

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery(), true, false);
        $entries = new Pagerfanta($pagerAdapter);

        $perPage = $user->getConfig()->getFeedLimit() ?: $this->getParameter('wallabag_core.feed_limit');
        $entries->setMaxPerPage($perPage);

        $url = $this->generateUrl(
            $type . '_feed',
            [
                'username' => $user->getUsername(),
                'token' => $user->getConfig()->getFeedToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        try {
            $entries->setCurrentPage((int) $page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($url . '/' . $entries->getNbPages());
            }
        }

        return $this->render('@WallabagCore/themes/common/Entry/entries.xml.twig', [
            'type' => $type,
            'url' => $url,
            'entries' => $entries,
            'user' => $user->getUsername(),
            'domainName' => $this->getParameter('domain_name'),
            'version' => $this->getParameter('wallabag_core.version'),
            'isFeedUseSource' => $user->getConfig()->isFeedUseSource(),
        ],
        new Response('', 200, ['Content-Type' => 'application/atom+xml'])
        );
    }
}
