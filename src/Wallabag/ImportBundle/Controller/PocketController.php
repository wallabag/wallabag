<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PocketController extends Controller
{
    /**
     * @Route("/import/pocket", name="import_pocket")
     */
    public function indexAction()
    {
        return $this->render('WallabagImportBundle:Pocket:index.html.twig', []);
    }

    /**
     * @Route("/import/pocket/auth", name="import_pocket_auth")
     */
    public function authAction()
    {
        $requestToken = $this->get('wallabag_import.pocket.import')
            ->getRequestToken($this->generateUrl('import', [], true));

        $this->get('session')->set('import.pocket.code', $requestToken);

        return $this->redirect(
            'https://getpocket.com/auth/authorize?request_token='.$requestToken.'&redirect_uri='.$this->generateUrl('import_pocket_callback', [], true),
            301
        );
    }

    /**
     * @Route("/import/pocket/callback", name="import_pocket_callback")
     */
    public function callbackAction()
    {
        $message = 'Import failed, please try again.';
        $pocket = $this->get('wallabag_import.pocket.import');

        // something bad happend on pocket side
        if (false === $pocket->authorize($this->get('session')->get('import.pocket.code'))) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                $message
            );

            return $this->redirect($this->generateUrl('import_pocket'));
        }

        if (true === $pocket->import()) {
            $summary = $pocket->getSummary();
            $message = 'Import summary: '.$summary['imported'].' imported, '.$summary['skipped'].' already saved.';
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $message
        );

        return $this->redirect($this->generateUrl('homepage'));
    }
}
