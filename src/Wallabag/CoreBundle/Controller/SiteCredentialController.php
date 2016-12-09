<?php

namespace Wallabag\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Entity\SiteCredential;

/**
 * SiteCredential controller.
 *
 * @Route("/site-credentials")
 */
class SiteCredentialController extends Controller
{
    /**
     * Lists all User entities.
     *
     * @Route("/", name="site_credentials_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $credentials = $em->getRepository('WallabagCoreBundle:SiteCredential')->findByUser($this->getUser());

        return $this->render('WallabagCoreBundle:SiteCredential:index.html.twig', array(
            'credentials' => $credentials,
        ));
    }

    /**
     * Creates a new site credential entity.
     *
     * @Route("/new", name="site_credentials_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $credential = new SiteCredential($this->getUser());

        $form = $this->createForm('Wallabag\CoreBundle\Form\Type\SiteCredentialType', $credential);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($credential);
            $em->flush($credential);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.site_credential.notice.added', ['%host%' => $credential->getHost()])
            );

            return $this->redirectToRoute('site_credentials_edit', array('id' => $credential->getId()));
        }

        return $this->render('WallabagCoreBundle:SiteCredential:new.html.twig', array(
            'credential' => $credential,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing site credential entity.
     *
     * @Route("/{id}/edit", name="site_credentials_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, SiteCredential $siteCredential)
    {
        $this->checkUserAction($siteCredential);

        $deleteForm = $this->createDeleteForm($siteCredential);
        $editForm = $this->createForm('Wallabag\CoreBundle\Form\Type\SiteCredentialType', $siteCredential);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($siteCredential);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.site_credential.notice.updated', ['%host%' => $siteCredential->getHost()])
            );

            return $this->redirectToRoute('site_credentials_edit', array('id' => $siteCredential->getId()));
        }

        return $this->render('WallabagCoreBundle:SiteCredential:edit.html.twig', array(
            'credential' => $siteCredential,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a site credential entity.
     *
     * @Route("/{id}", name="site_credentials_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, SiteCredential $siteCredential)
    {
        $this->checkUserAction($siteCredential);

        $form = $this->createDeleteForm($siteCredential);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.site_credential.notice.deleted', ['%host%' => $siteCredential->getHost()])
            );

            $em = $this->getDoctrine()->getManager();
            $em->remove($siteCredential);
            $em->flush();
        }

        return $this->redirectToRoute('site_credentials_index');
    }

    /**
     * Creates a form to delete a site credential entity.
     *
     * @param SiteCredential $siteCredential The site credential entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(SiteCredential $siteCredential)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('site_credentials_delete', array('id' => $siteCredential->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Check if the logged user can manage the given site credential.
     *
     * @param SiteCredential $siteCredential The site credential entity
     */
    private function checkUserAction(SiteCredential $siteCredential)
    {
        if (null === $this->getUser() || $this->getUser()->getId() != $siteCredential->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this site credential.');
        }
    }
}
