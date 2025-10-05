<?php

namespace Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Entity\IgnoreOriginInstanceRule;
use Wallabag\Form\Type\IgnoreOriginInstanceRuleType;
use Wallabag\Repository\IgnoreOriginInstanceRuleRepository;

/**
 * IgnoreOriginInstanceRuleController controller.
 */
class IgnoreOriginInstanceRuleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * Lists all IgnoreOriginInstanceRule entities.
     */
    #[Route(path: '/ignore-origin-instance-rules', name: 'ignore_origin_instance_rules_index', methods: ['GET'])]
    #[IsGranted('LIST_IGNORE_ORIGIN_INSTANCE_RULES')]
    public function indexAction(IgnoreOriginInstanceRuleRepository $repository)
    {
        $rules = $repository->findAll();

        return $this->render('IgnoreOriginInstanceRule/index.html.twig', [
            'rules' => $rules,
        ]);
    }

    /**
     * Creates a new ignore origin instance rule entity.
     *
     * @return Response
     */
    #[Route(path: '/ignore-origin-instance-rules/new', name: 'ignore_origin_instance_rules_new', methods: ['GET', 'POST'])]
    #[IsGranted('CREATE_IGNORE_ORIGIN_INSTANCE_RULES')]
    public function newAction(Request $request)
    {
        $ignoreOriginInstanceRule = new IgnoreOriginInstanceRule();

        $form = $this->createForm(IgnoreOriginInstanceRuleType::class, $ignoreOriginInstanceRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($ignoreOriginInstanceRule);
            $this->entityManager->flush();

            $this->addFlash(
                'notice',
                $this->translator->trans('flashes.ignore_origin_instance_rule.notice.added')
            );

            return $this->redirectToRoute('ignore_origin_instance_rules_index');
        }

        return $this->render('IgnoreOriginInstanceRule/new.html.twig', [
            'rule' => $ignoreOriginInstanceRule,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing ignore origin instance rule entity.
     *
     * @return Response
     */
    #[Route(path: '/ignore-origin-instance-rules/{id}/edit', name: 'ignore_origin_instance_rules_edit', methods: ['GET', 'POST'])]
    #[IsGranted('EDIT', subject: 'ignoreOriginInstanceRule')]
    public function editAction(Request $request, IgnoreOriginInstanceRule $ignoreOriginInstanceRule)
    {
        $deleteForm = $this->createDeleteForm($ignoreOriginInstanceRule);
        $editForm = $this->createForm(IgnoreOriginInstanceRuleType::class, $ignoreOriginInstanceRule);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->entityManager->persist($ignoreOriginInstanceRule);
            $this->entityManager->flush();

            $this->addFlash(
                'notice',
                $this->translator->trans('flashes.ignore_origin_instance_rule.notice.updated')
            );

            return $this->redirectToRoute('ignore_origin_instance_rules_index');
        }

        return $this->render('IgnoreOriginInstanceRule/edit.html.twig', [
            'rule' => $ignoreOriginInstanceRule,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Deletes a site credential entity.
     *
     * @return RedirectResponse
     */
    #[Route(path: '/ignore-origin-instance-rules/{id}', name: 'ignore_origin_instance_rules_delete', methods: ['DELETE'])]
    #[IsGranted('DELETE', subject: 'ignoreOriginInstanceRule')]
    public function deleteAction(Request $request, IgnoreOriginInstanceRule $ignoreOriginInstanceRule)
    {
        $form = $this->createDeleteForm($ignoreOriginInstanceRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'notice',
                $this->translator->trans('flashes.ignore_origin_instance_rule.notice.deleted')
            );

            $this->entityManager->remove($ignoreOriginInstanceRule);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('ignore_origin_instance_rules_index');
    }

    /**
     * Creates a form to delete a ignore origin instance rule entity.
     *
     * @param IgnoreOriginInstanceRule $ignoreOriginInstanceRule The ignore origin instance rule entity
     *
     * @return FormInterface The form
     */
    private function createDeleteForm(IgnoreOriginInstanceRule $ignoreOriginInstanceRule)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ignore_origin_instance_rules_delete', ['id' => $ignoreOriginInstanceRule->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
