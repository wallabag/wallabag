<?php

namespace Wallabag\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Wallabag\CoreBundle\Entity\IgnoreOriginInstanceRule;
use Wallabag\CoreBundle\Form\Type\IgnoreOriginInstanceRuleType;
use Wallabag\CoreBundle\Repository\IgnoreOriginInstanceRuleRepository;

/**
 * IgnoreOriginInstanceRuleController controller.
 *
 * @Route("/ignore-origin-instance-rules")
 */
class IgnoreOriginInstanceRuleController extends Controller
{
    /**
     * Lists all IgnoreOriginInstanceRule entities.
     *
     * @Route("/", name="ignore_origin_instance_rules_index", methods={"GET"})
     */
    public function indexAction()
    {
        $rules = $this->get(IgnoreOriginInstanceRuleRepository::class)->findAll();

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
            $em = $this->getDoctrine()->getManager();
            $em->persist($ignoreOriginInstanceRule);
            $em->flush();

            $this->get(SessionInterface::class)->getFlashBag()->add(
                'notice',
                $this->get(TranslatorInterface::class)->trans('flashes.ignore_origin_instance_rule.notice.added')
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
            $em = $this->getDoctrine()->getManager();
            $em->persist($ignoreOriginInstanceRule);
            $em->flush();

            $this->get(SessionInterface::class)->getFlashBag()->add(
                'notice',
                $this->get(TranslatorInterface::class)->trans('flashes.ignore_origin_instance_rule.notice.updated')
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
            $this->get(SessionInterface::class)->getFlashBag()->add(
                'notice',
                $this->get(TranslatorInterface::class)->trans('flashes.ignore_origin_instance_rule.notice.deleted')
            );

            $em = $this->getDoctrine()->getManager();
            $em->remove($ignoreOriginInstanceRule);
            $em->flush();
        }

        return $this->redirectToRoute('ignore_origin_instance_rules_index');
    }

    /**
     * Creates a form to delete a ignore origin instance rule entity.
     *
     * @param IgnoreOriginInstanceRule $ignoreOriginInstanceRule The ignore origin instance rule entity
     *
     * @return Form The form
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
