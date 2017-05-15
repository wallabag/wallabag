<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\CoreBundle\Entity\Notification;

class NotificationsController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/notifications", name="notifications-all")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAllNotificationsAction(Request $request)
    {
        $notifications = $this->getDoctrine()->getRepository('WallabagCoreBundle:Notification')->findByUser($this->getUser());

        return $this->render('WallabagCoreBundle:Notification:notifications.html.twig', ['notifications' => $notifications]);
    }

    /**
     * @Route("/notifications/readall", name="notification-archive-all")
     *
     * @param Request $request
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
}
