<?php

namespace Wallabag\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\CoreBundle\Entity\IgnoreOriginInstanceRule;

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
        $rules = $this->get('wallabag_core.ignore_origin_instance_rule_repository')->findAll();

        return $this->render('WallabagCoreBundle:IgnoreOriginInstanceRule:index.html.twig', [
            'rules' => $rules,
        ]);
    }

    /**
     * Creates a new ignore origin instance rule entity.
     *
     * @Route("/new", name="ignore_origin_instance_rules_new", methods={"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $ignoreOriginInstanceRule = new IgnoreOriginInstanceRule();

        $form = $this->createForm('Wallabag\CoreBundle\Form\Type\IgnoreOriginInstanceRuleType', $ignoreOriginInstanceRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($ignoreOriginInstanceRule);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.ignore_origin_instance_rule.notice.added')
            );

            return $this->redirectToRoute('ignore_origin_instance_rules_index');
        }

        return $this->render('WallabagCoreBundle:IgnoreOriginInstanceRule:new.html.twig', [
            'rule' => $ignoreOriginInstanceRule,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing ignore origin instance rule entity.
     *
     * @Route("/{id}/edit", name="ignore_origin_instance_rules_edit", methods={"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, IgnoreOriginInstanceRule $ignoreOriginInstanceRule)
    {
        $deleteForm = $this->createDeleteForm($ignoreOriginInstanceRule);
        $editForm = $this->createForm('Wallabag\CoreBundle\Form\Type\IgnoreOriginInstanceRuleType', $ignoreOriginInstanceRule);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($ignoreOriginInstanceRule);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.ignore_origin_instance_rule.notice.updated')
            );

            return $this->redirectToRoute('ignore_origin_instance_rules_index');
        }

        return $this->render('WallabagCoreBundle:IgnoreOriginInstanceRule:edit.html.twig', [
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, IgnoreOriginInstanceRule $ignoreOriginInstanceRule)
    {
        $form = $this->createDeleteForm($ignoreOriginInstanceRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.ignore_origin_instance_rule.notice.deleted')
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
     * @return \Symfony\Component\Form\Form The form
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
