<?php

namespace Wallabag\CoreBundle\Controller;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\UserBundle\Entity\User;

class RssController extends Controller
{
    /**
     * Shows unread entries for current user.
     *
     * @Route("/{username}/{token}/unread.xml", name="unread_rss", defaults={"_format"="xml"})
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showUnreadRSSAction(Request $request, User $user)
    {
        return $this->showEntries('unread', $user, $request->query->get('page', 1));
    }

    /**
     * Shows read entries for current user.
     *
     * @Route("/{username}/{token}/archive.xml", name="archive_rss", defaults={"_format"="xml"})
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArchiveRSSAction(Request $request, User $user)
    {
        return $this->showEntries('archive', $user, $request->query->get('page', 1));
    }

    /**
     * Shows starred entries for current user.
     *
     * @Route("/{username}/{token}/starred.xml", name="starred_rss", defaults={"_format"="xml"})
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showStarredRSSAction(Request $request, User $user)
    {
        return $this->showEntries('starred', $user, $request->query->get('page', 1));
    }

    /**
     * Shows all entries for current user.
     *
     * @Route("/{username}/{token}/all.xml", name="all_rss", defaults={"_format"="xml"})
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAllRSSAction(Request $request, User $user)
    {
        return $this->showEntries('all', $user, $request->query->get('page', 1));
    }

    /**
     * Shows entries associated to a tag for current user.
     *
     * @Route("/{username}/{token}/tags/{slug}.xml", name="tag_rss", defaults={"_format"="xml"})
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_rsstoken_converter")
     * @ParamConverter("tag", options={"mapping": {"slug": "slug"}})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showTagsAction(Request $request, User $user, Tag $tag)
    {
        $page = $request->query->get('page', 1);

        $url = $this->generateUrl(
            'tag_rss',
            [
                'username' => $user->getUsername(),
                'token' => $user->getConfig()->getRssToken(),
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
                'url_html' => $this->generateUrl('tag_entries', ['slug' => $tag->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'type' => 'tag (' . $tag->getLabel() . ')',
                'url' => $url,
                'entries' => $entries,
            ],
            new Response('', 200, ['Content-Type' => 'application/rss+xml'])
        );
    }

    /**
     * Global method to retrieve entries depending on the given type
     * It returns the response to be send.
     *
     * @param string $type Entries type: unread, starred or archive
     * @param User   $user
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

        $perPage = $user->getConfig()->getRssLimit() ?: $this->getParameter('wallabag_core.rss_limit');
        $entries->setMaxPerPage($perPage);

        $url = $this->generateUrl(
            $type . '_rss',
            [
                'username' => $user->getUsername(),
                'token' => $user->getConfig()->getRssToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        try {
            $entries->setCurrentPage((int) $page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($url . '?page=' . $entries->getNbPages(), 302);
            }
        }

        return $this->render(
            '@WallabagCore/themes/common/Entry/entries.xml.twig',
            [
                'url_html' => $this->generateUrl($type, [], UrlGeneratorInterface::ABSOLUTE_URL),
                'type' => $type,
                'url' => $url,
                'entries' => $entries,
            ],
            new Response('', 200, ['Content-Type' => 'application/rss+xml'])
        );
    }
}
