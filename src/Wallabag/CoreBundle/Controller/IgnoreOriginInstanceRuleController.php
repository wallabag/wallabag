<?php

namespace Wallabag\CoreBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\CoreBundle\Entity\IgnoreOriginInstanceRule;
use Wallabag\CoreBundle\Form\Type\IgnoreOriginInstanceRuleType;
use Wallabag\CoreBundle\Repository\IgnoreOriginInstanceRuleRepository;

/**
 * IgnoreOriginInstanceRuleController controller.
 *
 * @Route("/ignore-origin-instance-rules")
 */
class IgnoreOriginInstanceRuleController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Lists all IgnoreOriginInstanceRule entities.
     *
     * @Route("/", name="ignore_origin_instance_rules_index", methods={"GET"})
     */
    public function indexAction(IgnoreOriginInstanceRuleRepository $repository)
    {
        $rules = $repository->findAll();

        return $this->render('@WallabagCore/IgnoreOriginInstanceRule/index.html.twig', [
            'rules' => $rules,
        ]);
    }

    /**
     * Creates a new ignore origin instance rule entity.
     *
     * @Route("/new", name="ignore_origin_instance_rules_new", methods={"GET", "POST"})
     *
     * @return Response
     */
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

        return $this->render('@WallabagCore/IgnoreOriginInstanceRule/new.html.twig', [
            'rule' => $ignoreOriginInstanceRule,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing ignore origin instance rule entity.
     *
     * @Route("/{id}/edit", name="ignore_origin_instance_rules_edit", methods={"GET", "POST"})
     *
     * @return Response
     */
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

        return $this->render('@WallabagCore/IgnoreOriginInstanceRule/edit.html.twig', [
            'rule' => $ignoreOriginInstanceRule,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Deletes a site credential entity.
     *
     * @Route("/{id}", name="ignore_origin_instance_rules_delete", methods={"DELETE"})
     *
     * @return RedirectResponse
     */
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
