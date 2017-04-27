<?php

namespace Wallabag\GroupBundle\Controller;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Strut\StrutBundle\Service\Sha256Salted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\GroupBundle\Entity\Group;
use Wallabag\GroupBundle\Entity\UserGroup;
use Wallabag\GroupBundle\Form\GroupType;
use Wallabag\GroupBundle\Form\NewGroupType;
use Wallabag\GroupBundle\Form\UserGroupType;
use Wallabag\UserBundle\Entity\User;

/**
 * Group controller.
 */
class ManageController extends Controller
{
    /**
     * Lists all public Group entities.
     *
     * @Route("/{page}", requirements={"page" = "\d+"}, name="group_index", defaults={"page" = "1"})
     * @Method("GET")
     */
    public function indexAction($page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $groups = $em->getRepository('WallabagGroupBundle:Group')->findPublicGroups();

        $pagerAdapter = new DoctrineORMAdapter($groups->getQuery(), true, false);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(1);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('group_index', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }

        return $this->render('WallabagGroupBundle:Manage:index.html.twig', array(
            'groups' => $pagerFanta,
            'currentPage' => $page,
        ));
    }

    /**
     * Creates a new Group entity.
     *
     * @Route("/new", name="group_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $group = new Group();

        $form = $this->createForm(NewGroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($group->getAcceptSystem() == Group::ACCESS_PASSWORD) {
                /** @var Sha256Salted $encoder */
                $encoder = $this->get('sha256salted_encoder');
                $password = $encoder->encodePassword($group->getPassword(), $this->getParameter('secret'));
                $group->setPassword($password);
            }

            $em->persist($group);

            $groupUser = new UserGroup($this->getUser(), $group, Group::ROLE_ADMIN);
            $groupUser->setAccepted(true);
            $em->persist($groupUser);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.group.notice.added', ['%name%' => $group->getName()])
            );

            return $this->redirectToRoute('group_edit', array('id' => $group->getId()));
        }

        return $this->render('WallabagGroupBundle:Manage:new.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Group entity.
     *
     * @Route("/{id}/edit", name="group_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Group $group)
    {
        if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_ADMIN) {
            $this->createAccessDeniedException();
        }

        $deleteForm = $this->createDeleteForm($group);
        $editForm = $this->createForm(GroupType::class, $group);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($group->getAcceptSystem() === Group::ACCESS_PASSWORD) {
                $encoder = $this->get('sha256salted_encoder');
                $password = $encoder->encodePassword($group->getPlainPassword(), $this->getParameter('secret'));
                $group->setPassword($password);
            }

            $em->persist($group);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.group.notice.updated', ['%name%' => $group->getName()])
            );

            return $this->redirectToRoute('group_edit', array('id' => $group->getId()));
        }

        return $this->render('WallabagGroupBundle:Manage:edit.html.twig', array(
            'group' => $group,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Group entity.
     *
     * @Route("/{id}", name="group_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Group $group)
    {
        $form = $this->createDeleteForm($group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.group.notice.deleted', ['%name%' => $group->getName()])
            );

            $em = $this->getDoctrine()->getManager();
            $em->remove($group);
            $em->flush();
        }

        return $this->redirectToRoute('group_index');
    }

    /**
     * Creates a form to delete a Group entity.
     *
     * @param Group $group The Group entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Group $group)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('group_delete', array('id' => $group->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * @Route("/group-user-exclude/{group}/{user}", name="group-user-exclude")
     *
     * @param Group $group
     * @param User  $user
     *
     * @return Response
     */
    public function excludeMemberAction(Group $group, User $user)
    {
        $logger = $this->get('logger');
        $logger->info('User '.$this->getUser()->getUsername().' wants to exclude user '.$user->getUsername().' from group '.$group->getName());

        if (!$this->getUser()->inGroup($group) || $this->getUser()->getGroupRoleForUser($group) < Group::ROLE_MANAGE_USERS) {
            $logger->info('User '.$this->getUser()->getUsername().' has not enough rights on group '.$group->getName().' to exclude user '.$user->getUsername());
            throw $this->createAccessDeniedException();
        }

        if ($user->inGroup($group) && $user->getGroupRoleForUser($group) < Group::ROLE_ADMIN) {
            $em = $this->getDoctrine()->getManager();

            $logger->info('Removing user '.$this->getUser()->getUsername().' from group '.$group->getName());
            $em->remove($this->getUser()->getUserGroupFromGroup($group));

            $em->flush();

            return $this->redirectToRoute('group-manage', ['group' => $group->getId()]);
        }
        throw $this->createAccessDeniedException();
    }

	/**
	 * @Route("/join/{group}", name="group_join")
	 * @param Group $group
	 * @return Response
	 */
	public function joinGroupAction(Group $group): Response
	{
		$em = $this->getDoctrine()->getManager();

		if ($group->getAcceptSystem() === Group::ACCESS_PASSWORD) {
			return $this->redirectToRoute('group_password', ['group' => $group->getId()]);
		}
		$this->getUser()->addAGroup($group, $group->getDefaultRole());

		$em->flush();

		return $this->redirect($this->generateUrl('group_index'), 302);
	}

	/**
	 * @Route("/manage/{group}/{page}", name="group-manage", defaults={"page" = "1"})
	 * @param Group $group
	 * @return Response
	 */
	public function manageGroupUsersAction(Group $group, int $page): Response
	{
		if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_MANAGE_USERS) {
			$this->createAccessDeniedException();
		}

		$members = $this->get('wallabag_user.user_repository')->findGroupMembers($group->getId());

		$pagerAdapter = new DoctrineORMAdapter($members->getQuery(), true, false);
		$pagerFanta = new Pagerfanta($pagerAdapter);
		$pagerFanta->setMaxPerPage(9);

		try {
			$pagerFanta->setCurrentPage($page);
		} catch (OutOfRangeCurrentPageException $e) {
			if ($page > 1) {
				return $this->redirect($this->generateUrl('groups', ['page' => $pagerFanta->getNbPages()]), 302);
			}
		}

		return $this->render('WallabagGroupBundle:Manage:manage.html.twig', [
			'members' => $pagerFanta,
			'group' => $group,
			'currentPage' => $page,
		]);
	}

	/**
	 * @Route("/leave/{group}", name="group_leave")
	 * @param Group $group
	 * @return Response
	 */
	public function leaveGroupAction(Group $group): Response
	{
		$logger = $this->get('logger');
		$em = $this->getDoctrine()->getManager();
		$removeGroup = false;

		if ($this->getUser()->getGroupRoleForUser($group) == Group::ROLE_ADMIN) {
			$logger->info('User ' . $this->getUser()->getUsername() . ' is the admin for group ' . $group->getName());
			$newUser = $group->getUsers()->first();
			$newUser->setGroupRole($group, Group::ROLE_ADMIN);
			$logger->info('The new admin for group ' . $group->getName() . ' is user ' . $newUser->getUsername());
		}

		if ($group->getUsers()->count() <= 1) {
			$logger->info('User ' . $this->getUser()->getUsername() . ' was the last one on the group ' . $group->getName() . ' so it will be deleted');
			$removeGroup = true;
		}

		$logger->info('Removing user ' . $this->getUser()->getUsername() . ' from group ' . $group->getName());
		$em->remove($this->getUser()->getUserGroupFromGroup($group));

		if ($removeGroup) {
			$logger->info("Removing group " . $group->getName() . " as it doesn't contains users anymore");
			$em->remove($group);
		}

		$em->flush();
		return $this->redirect($this->generateUrl('groups'), 302);
	}

	/**
	 * @Route("/requests/{group}/{page}", name="group-requests", defaults={"page" = "1"})
	 * @param Request $request
	 * @param int $page
	 * @return Response
	 */
	public function showRequestsAction(Group $group, int $page): Response
	{
		if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_MANAGE_USERS) {
			$this->createAccessDeniedException();
		}

		$requests = $group->getRequests();
		$pagerAdapter = new ArrayAdapter($requests->toArray());

		$pagerFanta = new Pagerfanta($pagerAdapter);
		$pagerFanta->setMaxPerPage(9);

		try {
			$pagerFanta->setCurrentPage($page);
		} catch (OutOfRangeCurrentPageException $e) {
			if ($page > 1) {
				return $this->redirect($this->generateUrl('groups', ['page' => $pagerFanta->getNbPages()]), 302);
			}
		}

		return $this->render('WallabagGroupBundle:Manage:requests.html.twig', [
			'requests' => $pagerFanta,
			'group' => $group,
			'currentPage' => $page,
		]);
	}

	/**
	 * @Route("/activate/{group}/{user}/{accept}", name="group-activate", requirements={"accept" = "\d+"})
	 * @param Group $group
	 * @param User $user
	 * @param $accept
	 * @return Response
	 */
	public function postRequestAction(Group $group, User $user, $accept): Response
	{
		if (!$this->getUser() < Group::ROLE_MANAGE_USERS) {
			$this->createAccessDeniedException("You don't have the rights to do this");
		}

		$em = $this->getDoctrine()->getManager();

		$accept = $accept == 1;
		$user->getUserGroupFromGroup($group)->setAccepted($accept);
		if (!$accept) {
			$em->remove($user->getUserGroupFromGroup($group));
		}

		$em->flush();

		return $this->redirectToRoute('group_index');
	}

	/**
	 * @Route("/user-edit/{group}/{user}", name="group-user-edit")
	 * @param Request $request
	 * @param Group $group
	 * @param User $user
	 * @return Response
	 */
	public function editGroupUsersAction(Request $request, Group $group, User $user): Response
	{
		if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_MANAGE_USERS) {
			$this->createAccessDeniedException();
		}

		$groupUser = $user->getUserGroupFromGroup($group);
		$editForm = $this->createForm(UserGroupType::class, $groupUser);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($groupUser);
			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'notice',
				$this->get('translator')->trans('flashes.group.notice.user.edited', ['%user%' => $user->getUsername(), '%group%' => $group->getName()])
			);

			return $this->redirectToRoute('group-manage', ['group' => $group->getId()]);
		}

		return $this->render('WallabagGroupBundle:Manage:edit_user.html.twig', array(
			'user' => $user,
			'group' => $group,
			'edit_form' => $editForm->createView(),
		));
	}
}
