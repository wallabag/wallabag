<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PocketController extends Controller
{
    /**
     * @Route("/pocket", name="import_pocket")
     */
    public function indexAction()
    {
        $pocket = $this->getPocketImportService();
        $form = $this->createFormBuilder($pocket)
            ->add('mark_as_read', CheckboxType::class, [
                'label' => 'import.form.mark_as_read_label',
                'required' => false,
            ])
            ->getForm();

        return $this->render('WallabagImportBundle:Pocket:index.html.twig', [
            'import' => $this->getPocketImportService(),
            'has_consumer_key' => '' === trim($this->getUser()->getConfig()->getPocketConsumerKey()) ? false : true,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/pocket/auth", name="import_pocket_auth")
     */
    public function authAction(Request $request)
    {
        $requestToken = $this->getPocketImportService()
            ->getRequestToken($this->generateUrl('import', [], UrlGeneratorInterface::ABSOLUTE_URL));

        if (false === $requestToken) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'flashes.import.notice.failed'
            );

            return $this->redirect($this->generateUrl('import_pocket'));
        }

        $form = $request->request->get('form');

        $this->get('session')->set('import.pocket.code', $requestToken);
        if (null !== $form && \array_key_exists('mark_as_read', $form)) {
            $this->get('session')->set('mark_as_read', $form['mark_as_read']);
        }

        return $this->redirect(
            'https://getpocket.com/auth/authorize?request_token=' . $requestToken . '&redirect_uri=' . $this->generateUrl('import_pocket_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
            301
        );
    }

    /**
     * @Route("/pocket/callback", name="import_pocket_callback")
     */
    public function callbackAction()
    {
        $message = 'flashes.import.notice.failed';
        $pocket = $this->getPocketImportService();

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
            $message = $this->get('translator')->trans('flashes.import.notice.summary', [
                '%imported%' => null !== $summary && \array_key_exists('imported', $summary) ? $summary['imported'] : 0,
                '%skipped%' => null !== $summary && \array_key_exists('skipped', $summary) ? $summary['skipped'] : 0,
            ]);

            if (null !== $summary && \array_key_exists('queued', $summary) && 0 < $summary['queued']) {
                $message = $this->get('translator')->trans('flashes.import.notice.summary_with_queue', [
                    '%queued%' => $summary['queued'],
                ]);
            }
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $message
        );

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Return Pocket Import Service with or without RabbitMQ enabled.
     *
     * @return \Wallabag\ImportBundle\Import\PocketImport
     */
    private function getPocketImportService()
    {
        $pocket = $this->get('wallabag_import.pocket.import');
        $pocket->setUser($this->getUser());

        if ($this->get('craue_config')->get('import_with_rabbitmq')) {
            $pocket->setProducer($this->get('old_sound_rabbit_mq.import_pocket_producer'));
        } elseif ($this->get('craue_config')->get('import_with_redis')) {
            $pocket->setProducer($this->get('wallabag_import.producer.redis.pocket'));
        }

        return $pocket;
    }
}
