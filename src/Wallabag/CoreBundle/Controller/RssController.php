<?php

namespace Wallabag\CoreBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    public function showUnreadAction(Request $request, User $user)
    {
        return $this->showEntries('unread', $user, $request->query->get('page', 1));
    }

    /**
     * Shows read entries for current user.
     *
     * @Route("/{username}/{token}/archive.xml", name="archive_rss")
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArchiveAction(Request $request, User $user)
    {
        return $this->showEntries('archive', $user, $request->query->get('page', 1));
    }

    /**
     * Shows starred entries for current user.
     *
     * @Route("/{username}/{token}/starred.xml", name="starred_rss")
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showStarredAction(Request $request, User $user)
    {
        return $this->showEntries('starred', $user, $request->query->get('page', 1));
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

            default:
                throw new \InvalidArgumentException(sprintf('Type "%s" is not implemented.', $type));
        }

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery(), true, false);
        $entries = new Pagerfanta($pagerAdapter);

        $perPage = $user->getConfig()->getRssLimit() ?: $this->getParameter('wallabag_core.rss_limit');
        $entries->setMaxPerPage($perPage);

        $url = $this->generateUrl(
            $type.'_rss',
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
                return $this->redirect($url.'?page='.$entries->getNbPages(), 302);
            }
        }

        return $this->render('@WallabagCore/themes/common/Entry/entries.xml.twig', [
            'type' => $type,
            'url' => $url,
            'entries' => $entries,
        ]);
    }
}
