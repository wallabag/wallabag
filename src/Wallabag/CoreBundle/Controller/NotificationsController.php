<?php

namespace Wallabag\CoreBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\CoreBundle\Entity\Notification;

class NotificationsController extends Controller
{
    /**
     * @Route("/notifications/{page}", name="notifications-all", defaults={"page" = "1"})
     *
     * @param int $page
     *
     * @return Response
     */
    public function getAllNotificationsAction($page = 1)
    {
        $qb = $this->getDoctrine()->getRepository('WallabagCoreBundle:Notification')->getBuilderForNotificationsByUser($this->getUser()->getId());
        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery(), true, false);

        $notifications = new Pagerfanta($pagerAdapter);
        $notifications->setMaxPerPage($this->getParameter('wallabag_core.notifications_nb'));

        try {
            $notifications->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('notifications-all', ['page' => $notifications->getNbPages()]), 302);
            }
        }

        return $this->render('WallabagCoreBundle:Notification:notifications.html.twig', [
            'notifications' => $notifications,
            'currentPage' => $page,
        ]);
    }

    /**
     * @Route("/notifications/readall", name="notification-archive-all")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function markAllNotificationsAsReadAction(Request $request)
    {
        $this->getDoctrine()->getRepository('WallabagCoreBundle:Notification')->markAllAsReadForUser($this->getUser()->getId());

        return $this->redirectToRoute('notifications-all');
    }

    /**
     * @Route("/notifications/read/{notification}", name="notification-archive")
     *
     * @param Notification $notification
     *
     * @return Response
     */
    public function markNotificationsAsReadAction(Notification $notification)
    {
        $em = $this->getDoctrine()->getManager();

        $notification->setRead(true);

        $em->persist($notification);
        $em->flush();

        return $this->redirectToRoute('notifications-all');
    }

    /**
     * @Route("/notifications/read/{notification}/redirect", name="notification-archive-redirect", requirements={"notification" = "\d+"})
     *
     * @param Request      $request
     * @param Notification $notification
     */
    public function markNotificationAsReadAndRedirectAction(Request $request, Notification $notification)
    {
        $em = $this->getDoctrine()->getManager();

        $notification->setRead(true);

        $em->persist($notification);
        $em->flush();

        $redirection = $request->get('redirection');
        $this->redirect($redirection);
    }
}
