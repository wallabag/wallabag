<?php

namespace Wallabag\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\UserBundle\Entity\User;
use Wallabag\UserBundle\Form\NewUserType;
use Wallabag\UserBundle\Form\SearchUserType;
use Wallabag\UserBundle\Form\UserType;
use Wallabag\UserBundle\Repository\UserRepository;

/**
 * User controller.
 */
class ManageController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/new", name="user_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, UserManagerInterface $userManager, EventDispatcherInterface $eventDispatcher)
    {
        $user = $userManager->createUser();
        \assert($user instanceof User);
        // enable created user by default
        $user->setEnabled(true);

        $form = $this->createForm(NewUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);

            // dispatch a created event so the associated config will be created
            $event = new UserEvent($user, $request);
            $eventDispatcher->dispatch($event, FOSUserEvents::USER_CREATED);

            $this->addFlash(
                'notice',
                $this->translator->trans('flashes.user.notice.added', ['%username%' => $user->getUsername()])
            );

            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $this->render('@WallabagUser/Manage/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, User $user, UserManagerInterface $userManager, GoogleAuthenticatorInterface $googleAuthenticator)
    {
        $deleteForm = $this->createDeleteForm($user);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // `googleTwoFactor` isn't a field within the User entity, we need to define it's value in a different way
        if (true === $user->isGoogleAuthenticatorEnabled() && false === $form->isSubmitted()) {
            $form->get('googleTwoFactor')->setData(true);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // handle creation / reset of the OTP secret if checkbox changed from the previous state
            if (true === $form->get('googleTwoFactor')->getData() && false === $user->isGoogleAuthenticatorEnabled()) {
                $user->setGoogleAuthenticatorSecret($googleAuthenticator->generateSecret());
                $user->setEmailTwoFactor(false);
            } elseif (false === $form->get('googleTwoFactor')->getData() && true === $user->isGoogleAuthenticatorEnabled()) {
                $user->setGoogleAuthenticatorSecret(null);
            }

            $userManager->updateUser($user);

            $this->addFlash(
                'notice',
                $this->translator->trans('flashes.user.notice.updated', ['%username%' => $user->getUsername()])
            );

            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $this->render('@WallabagUser/Manage/edit.html.twig', [
            'user' => $user,
            'edit_form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
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
            $this->addFlash(
                'notice',
                $this->translator->trans('flashes.user.notice.deleted', ['%username%' => $user->getUsername()])
            );

            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @param int $page
     *
     * @Route("/list/{page}", name="user_index", defaults={"page" = 1})
     *
     * Default parameter for page is hardcoded (in duplication of the defaults from the Route)
     * because this controller is also called inside the layout template without any page as argument
     *
     * @return Response
     */
    public function searchFormAction(Request $request, UserRepository $userRepository, $page = 1)
    {
        $qb = $userRepository->createQueryBuilder('u');

        $form = $this->createForm(SearchUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchTerm = (isset($request->get('search_user')['term']) ? $request->get('search_user')['term'] : '');

            $qb = $userRepository->getQueryBuilderForSearch($searchTerm);
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

        return $this->render('@WallabagUser/Manage/index.html.twig', [
            'searchForm' => $form->createView(),
            'users' => $pagerFanta,
        ]);
    }

    /**
     * Create a form to delete a User entity.
     *
     * @param User $user The User entity
     *
     * @return FormInterface The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', ['id' => $user->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
