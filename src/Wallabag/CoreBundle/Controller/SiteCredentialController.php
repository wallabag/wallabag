<?php

namespace Wallabag\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\CoreBundle\Entity\SiteCredential;
use Wallabag\UserBundle\Entity\User;

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
     * @Route("/", name="site_credentials_index", methods={"GET"})
     */
    public function indexAction()
    {
        $this->isSiteCredentialsEnabled();

        $credentials = $this->get('wallabag_core.site_credential_repository')->findByUser($this->getUser());

        return $this->render('WallabagCoreBundle:SiteCredential:index.html.twig', [
            'credentials' => $credentials,
        ]);
    }

    /**
     * Creates a new site credential entity.
     *
     * @Route("/new", name="site_credentials_new", methods={"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $this->isSiteCredentialsEnabled();

        $credential = new SiteCredential($this->getUser());

        $form = $this->createForm('Wallabag\CoreBundle\Form\Type\SiteCredentialType', $credential);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $credential->setUsername($this->get('wallabag_core.helper.crypto_proxy')->crypt($credential->getUsername()));
            $credential->setPassword($this->get('wallabag_core.helper.crypto_proxy')->crypt($credential->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($credential);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.site_credential.notice.added', ['%host%' => $credential->getHost()])
            );

            return $this->redirectToRoute('site_credentials_index');
        }

        return $this->render('WallabagCoreBundle:SiteCredential:new.html.twig', [
            'credential' => $credential,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing site credential entity.
     *
     * @Route("/{id}/edit", name="site_credentials_edit", methods={"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, SiteCredential $siteCredential)
    {
        $this->isSiteCredentialsEnabled();

        $this->checkUserAction($siteCredential);

        $deleteForm = $this->createDeleteForm($siteCredential);
        $editForm = $this->createForm('Wallabag\CoreBundle\Form\Type\SiteCredentialType', $siteCredential);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $siteCredential->setUsername($this->get('wallabag_core.helper.crypto_proxy')->crypt($siteCredential->getUsername()));
            $siteCredential->setPassword($this->get('wallabag_core.helper.crypto_proxy')->crypt($siteCredential->getPassword()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($siteCredential);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.site_credential.notice.updated', ['%host%' => $siteCredential->getHost()])
            );

            return $this->redirectToRoute('site_credentials_index');
        }

        return $this->render('WallabagCoreBundle:SiteCredential:edit.html.twig', [
            'credential' => $siteCredential,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Deletes a site credential entity.
     *
     * @Route("/{id}", name="site_credentials_delete", methods={"DELETE"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, SiteCredential $siteCredential)
    {
        $this->isSiteCredentialsEnabled();

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
     * Throw a 404 if the feature is disabled.
     */
    private function isSiteCredentialsEnabled()
    {
        if (!$this->get('craue_config')->get('restricted_access')) {
            throw $this->createNotFoundException('Feature "restricted_access" is disabled, controllers too.');
        }
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
            ->setAction($this->generateUrl('site_credentials_delete', ['id' => $siteCredential->getId()]))
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
        if (null === $this->getUser() || $this->getUser()->getId() !== $siteCredential->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this site credential.');
        }
    }
}
