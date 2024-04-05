<?php

namespace Wallabag\Controller;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Entity\SiteCredential;
use Wallabag\Entity\User;
use Wallabag\Form\Type\SiteCredentialType;
use Wallabag\Helper\CryptoProxy;
use Wallabag\Repository\SiteCredentialRepository;

/**
 * SiteCredential controller.
 *
 * @Route("/site-credentials")
 */
class SiteCredentialController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private CryptoProxy $cryptoProxy;
    private Config $craueConfig;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator, CryptoProxy $cryptoProxy, Config $craueConfig)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->cryptoProxy = $cryptoProxy;
        $this->craueConfig = $craueConfig;
    }

    /**
     * Lists all User entities.
     *
     * @Route("/", name="site_credentials_index", methods={"GET"})
     * @IsGranted("LIST_SITE_CREDENTIALS")
     */
    public function indexAction(SiteCredentialRepository $repository)
    {
        $this->isSiteCredentialsEnabled();

        $credentials = $repository->findByUser($this->getUser());

        return $this->render('SiteCredential/index.html.twig', [
            'credentials' => $credentials,
        ]);
    }

    /**
     * Creates a new site credential entity.
     *
     * @Route("/new", name="site_credentials_new", methods={"GET", "POST"})
     * @IsGranted("CREATE_SITE_CREDENTIALS")
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $this->isSiteCredentialsEnabled();

        $credential = new SiteCredential($this->getUser());

        $form = $this->createForm(SiteCredentialType::class, $credential);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $credential->setUsername($this->cryptoProxy->crypt($credential->getUsername()));
            $credential->setPassword($this->cryptoProxy->crypt($credential->getPassword()));

            $this->entityManager->persist($credential);
            $this->entityManager->flush();

            $this->addFlash(
                'notice',
                $this->translator->trans('flashes.site_credential.notice.added', ['%host%' => $credential->getHost()])
            );

            return $this->redirectToRoute('site_credentials_index');
        }

        return $this->render('SiteCredential/new.html.twig', [
            'credential' => $credential,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing site credential entity.
     *
     * @Route("/{id}/edit", name="site_credentials_edit", methods={"GET", "POST"})
     * @IsGranted("EDIT", subject="siteCredential")
     *
     * @return Response
     */
    public function editAction(Request $request, SiteCredential $siteCredential)
    {
        $this->isSiteCredentialsEnabled();

        $deleteForm = $this->createDeleteForm($siteCredential);
        $editForm = $this->createForm(SiteCredentialType::class, $siteCredential);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $siteCredential->setUsername($this->cryptoProxy->crypt($siteCredential->getUsername()));
            $siteCredential->setPassword($this->cryptoProxy->crypt($siteCredential->getPassword()));

            $this->entityManager->persist($siteCredential);
            $this->entityManager->flush();

            $this->addFlash(
                'notice',
                $this->translator->trans('flashes.site_credential.notice.updated', ['%host%' => $siteCredential->getHost()])
            );

            return $this->redirectToRoute('site_credentials_index');
        }

        return $this->render('SiteCredential/edit.html.twig', [
            'credential' => $siteCredential,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Deletes a site credential entity.
     *
     * @Route("/{id}", name="site_credentials_delete", methods={"DELETE"})
     * @IsGranted("DELETE", subject="siteCredential")
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, SiteCredential $siteCredential)
    {
        $this->isSiteCredentialsEnabled();

        $form = $this->createDeleteForm($siteCredential);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'notice',
                $this->translator->trans('flashes.site_credential.notice.deleted', ['%host%' => $siteCredential->getHost()])
            );

            $this->entityManager->remove($siteCredential);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('site_credentials_index');
    }

    /**
     * Throw a 404 if the feature is disabled.
     */
    private function isSiteCredentialsEnabled()
    {
        if (!$this->craueConfig->get('restricted_access')) {
            throw $this->createNotFoundException('Feature "restricted_access" is disabled, controllers too.');
        }
    }

    /**
     * Creates a form to delete a site credential entity.
     *
     * @param SiteCredential $siteCredential The site credential entity
     *
     * @return FormInterface The form
     */
    private function createDeleteForm(SiteCredential $siteCredential)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('site_credentials_delete', ['id' => $siteCredential->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
