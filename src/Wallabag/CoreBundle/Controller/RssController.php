<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\CoreBundle\Entity\User;
use Wallabag\CoreBundle\Entity\Entry;

class RssController extends Controller
{
    /**
     * Shows unread entries for current user.
     *
     * @Route("/{username}/{token}/unread.xml", name="unread_rss", defaults={"_format"="xml"})
     * @ParamConverter("user", class="WallabagCoreBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showUnreadAction(User $user)
    {
        $entries = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findUnreadByUser(
                $user->getId()
            );

        $perPage = $user->getConfig()->getRssLimit() ?: $this->container->getParameter('rss_limit');
        $entries->setMaxPerPage($perPage);

        return $this->render('WallabagCoreBundle:Entry:entries.xml.twig', array(
            'type' => 'unread',
            'entries' => $entries,
        ));
    }

    /**
     * Shows read entries for current user.
     *
     * @Route("/{username}/{token}/archive.xml", name="archive_rss")
     * @ParamConverter("user", class="WallabagCoreBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArchiveAction(User $user)
    {
        $entries = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findArchiveByUser(
                $user->getId()
            );

        $perPage = $user->getConfig()->getRssLimit() ?: $this->container->getParameter('rss_limit');
        $entries->setMaxPerPage($perPage);

        return $this->render('WallabagCoreBundle:Entry:entries.xml.twig', array(
            'type' => 'archive',
            'entries' => $entries,
        ));
    }

    /**
     * Shows starred entries for current user.
     *
     * @Route("/{username}/{token}/starred.xml", name="starred_rss")
     * @ParamConverter("user", class="WallabagCoreBundle:User", converter="username_rsstoken_converter")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showStarredAction(User $user)
    {
        $entries = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findStarredByUser(
                $user->getId()
            );

        $perPage = $user->getConfig()->getRssLimit() ?: $this->container->getParameter('rss_limit');
        $entries->setMaxPerPage($perPage);

        return $this->render('WallabagCoreBundle:Entry:entries.xml.twig', array(
            'type' => 'starred',
            'entries' => $entries,
        ));
    }
}
