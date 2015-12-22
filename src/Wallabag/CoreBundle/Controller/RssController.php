<?php

namespace Wallabag\CoreBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\CoreBundle\Entity\Entry;
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
    public function showUnreadAction(User $user)
    {
        return $this->showEntries('unread', $user);
    }

    /**
     * Shows read entries for current user.
     *
     * @Route("/{username}/{token}/archive.xml", name="archive_rss")
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArchiveAction(User $user)
    {
        return $this->showEntries('archive', $user);
    }

    /**
     * Shows starred entries for current user.
     *
     * @Route("/{username}/{token}/starred.xml", name="starred_rss")
     * @ParamConverter("user", class="WallabagUserBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showStarredAction(User $user)
    {
        return $this->showEntries('starred', $user);
    }

    /**
     * Global method to retrieve entries depending on the given type
     * It returns the response to be send.
     *
     * @param string $type Entries type: unread, starred or archive
     * @param User   $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function showEntries($type, User $user)
    {
        $repository = $this->getDoctrine()->getRepository('WallabagCoreBundle:Entry');

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

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery());
        $entries = new Pagerfanta($pagerAdapter);

        $perPage = $user->getConfig()->getRssLimit() ?: $this->container->getParameter('rss_limit');
        $entries->setMaxPerPage($perPage);

        return $this->render('WallabagCoreBundle:Entry:entries.xml.twig', array(
            'type' => $type,
            'entries' => $entries,
        ));
    }
}
