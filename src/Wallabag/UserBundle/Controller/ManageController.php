<?php

namespace Wallabag\UserBundle\Controller;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\UserBundle\Entity\User;
use Wallabag\UserBundle\Form\SearchUserType;

/**
 * User controller.
 */
class ManageController extends Controller
{
    /**
     * Creates a new User entity.
     *
     * @Route("/new", name="user_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->createUser();
        // enable created user by default
        $user->setEnabled(true);

        $form = $this->createEditForm('NewUserType', $user, $request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->handleOtp($form, $user);
            $userManager->updateUser($user);

            // dispatch a created event so the associated config will be created
            $event = new UserEvent($user, $request);
            $this->get('event_dispatcher')->dispatch(FOSUserEvents::USER_CREATED, $event);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.user.notice.added', ['%username%' => $user->getUsername()])
            );

            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $this->render('WallabagUserBundle:Manage:new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, User $user)
    {
        $userManager = $this->container->get('fos_user.user_manager');

        $deleteForm = $this->createDeleteForm($user);
        $form = $this->createEditForm('UserType', $user, $request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->handleOtp($form, $user);
            $userManager->updateUser($user);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.user.notice.updated', ['%username%' => $user->getUsername()])
            );

            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $this->render('WallabagUserBundle:Manage:edit.html.twig', [
            'user' => $user,
            'edit_form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'twofactor_auth' => $this->getParameter('twofactor_auth'),
        ]);
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.user.notice.deleted', ['%username%' => $user->getUsername()])
            );

            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @param Request $request
     * @param int     $page
     *
     * @Route("/list/{page}", name="user_index", defaults={"page" = 1})
     *
     * Default parameter for page is hardcoded (in duplication of the defaults from the Route)
     * because this controller is also called inside the layout template without any page as argument
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchFormAction(Request $request, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('WallabagUserBundle:User')->createQueryBuilder('u');

        $form = $this->createForm(SearchUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('logger')->info('searching users');

            $searchTerm = (isset($request->get('search_user')['term']) ? $request->get('search_user')['term'] : '');

            $qb = $em->getRepository('WallabagUserBundle:User')->getQueryBuilderForSearch($searchTerm);
        }

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery(), true, false);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(50);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('user_index', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }

        return $this->render('WallabagUserBundle:Manage:index.html.twig', [
            'searchForm' => $form->createView(),
            'users' => $pagerFanta,
        ]);
    }

    /**
     * Create a form to delete a User entity.
     *
     * @param User $user The User entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', ['id' => $user->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Create a form to create or edit a User entity.
     *
     * @param string  $type    Might be NewUserType or UserType
     * @param User    $user    The new / edit user
     * @param Request $request The request
     *
     * @return FormInterface
     */
    private function createEditForm($type, User $user, Request $request)
    {
        $form = $this->createForm('Wallabag\UserBundle\Form\\' . $type, $user);
        $form->handleRequest($request);

        // `googleTwoFactor` isn't a field within the User entity, we need to define it's value in a different way
        if (true === $user->isGoogleAuthenticatorEnabled() && false === $form->isSubmitted()) {
            $form->get('googleTwoFactor')->setData(true);
        }

        return $form;
    }

    /**
     * Handle OTP update, taking care to only have one 2fa enable at a time.
     *
     * @see  ConfigController
     *
     * @param FormInterface $form
     * @param User          $user
     *
     * @return User
     */
    private function handleOtp(FormInterface $form, User $user)
    {
        if (true === $form->get('googleTwoFactor')->getData() && false === $user->isGoogleAuthenticatorEnabled()) {
            $user->setGoogleAuthenticatorSecret($this->get('scheb_two_factor.security.google_authenticator')->generateSecret());
            $user->setEmailTwoFactor(false);

            return $user;
        }

        $user->setGoogleAuthenticatorSecret(null);

        return $user;
    }
}
