<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class PocketController extends Controller
{
    /**
     * @Route("/pocket", name="import_pocket")
     */
    public function indexAction()
    {
        $pocket = $this->get('wallabag_import.pocket.import');
        $form = $this->createFormBuilder($pocket)
            ->add('mark_as_read', CheckboxType::class, array(
                'label' => 'import.form.mark_as_read_label',
                'required' => false,
            ))
            ->getForm();

        return $this->render('WallabagImportBundle:Pocket:index.html.twig', [
            'import' => $this->get('wallabag_import.pocket.import'),
            'has_consumer_key' => '' == trim($this->get('craue_config')->get('pocket_consumer_key')) ? false : true,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/pocket/auth", name="import_pocket_auth")
     */
    public function authAction(Request $request)
    {
        $requestToken = $this->get('wallabag_import.pocket.import')
            ->getRequestToken($this->generateUrl('import', array(), UrlGeneratorInterface::ABSOLUTE_URL));

        $this->get('session')->set('import.pocket.code', $requestToken);
        $this->get('session')->set('mark_as_read', $request->request->get('form')['mark_as_read']);

        return $this->redirect(
            'https://getpocket.com/auth/authorize?request_token='.$requestToken.'&redirect_uri='.$this->generateUrl('import_pocket_callback', array(), UrlGeneratorInterface::ABSOLUTE_URL),
            301
        );
    }

    /**
     * @Route("/pocket/callback", name="import_pocket_callback")
     */
    public function callbackAction()
    {
        $message = 'flashes.import.notice.failed';
        $pocket = $this->get('wallabag_import.pocket.import');

        $markAsRead = $this->get('session')->get('mark_as_read');
        $this->get('session')->remove('mark_as_read');

        // something bad happend on pocket side
        if (false === $pocket->authorize($this->get('session')->get('import.pocket.code'))) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                $message
            );

            return $this->redirect($this->generateUrl('import_pocket'));
        }

        if (true === $pocket->setMarkAsRead($markAsRead)->import()) {
            $summary = $pocket->getSummary();
            $message = $this->get('translator')->trans('flashes.import.notice.summary', array(
                '%imported%' => $summary['imported'],
                '%skipped%' => $summary['skipped'],
            ));
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $message
        );

        return $this->redirect($this->generateUrl('homepage'));
    }
}
