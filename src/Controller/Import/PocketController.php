<?php

namespace Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Controller\AbstractController;
use Wallabag\Import\PocketImport;
use Wallabag\Redis\Producer as RedisProducer;

class PocketController extends AbstractController
{
    public function __construct(
        private readonly Config $craueConfig,
        private readonly RabbitMqProducer $rabbitMqProducer,
        private readonly RedisProducer $redisProducer,
        private readonly SessionInterface $session,
    ) {
    }

    #[Route(path: '/import/pocket', name: 'import_pocket', methods: ['GET'])]
    #[IsGranted('IMPORT_ENTRIES')]
    public function indexAction(PocketImport $pocketImport)
    {
        $pocket = $this->getPocketImportService($pocketImport);

        $form = $this->createFormBuilder($pocket)
            ->add('mark_as_read', CheckboxType::class, [
                'label' => 'import.form.mark_as_read_label',
                'required' => false,
            ])
            ->getForm();

        return $this->render('Import/Pocket/index.html.twig', [
            'import' => $pocket,
            'has_consumer_key' => '' === trim($this->getUser()->getConfig()->getPocketConsumerKey()) ? false : true,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/import/pocket/auth', name: 'import_pocket_auth', methods: ['POST'])]
    #[IsGranted('IMPORT_ENTRIES')]
    public function authAction(Request $request, PocketImport $pocketImport)
    {
        $requestToken = $this->getPocketImportService($pocketImport)
            ->getRequestToken($this->generateUrl('import', [], UrlGeneratorInterface::ABSOLUTE_URL));

        if (false === $requestToken) {
            $this->addFlash(
                'notice',
                'flashes.import.notice.failed'
            );

            return $this->redirect($this->generateUrl('import_pocket'));
        }

        $form = $request->request->all('form');

        $this->session->set('import.pocket.code', $requestToken);
        if (\array_key_exists('mark_as_read', $form)) {
            $this->session->set('mark_as_read', $form['mark_as_read']);
        }

        return $this->redirect(
            'https://getpocket.com/auth/authorize?request_token=' . $requestToken . '&redirect_uri=' . $this->generateUrl('import_pocket_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
            301
        );
    }

    #[Route(path: '/import/pocket/callback', name: 'import_pocket_callback', methods: ['GET'])]
    #[IsGranted('IMPORT_ENTRIES')]
    public function callbackAction(PocketImport $pocketImport, TranslatorInterface $translator)
    {
        $message = 'flashes.import.notice.failed';
        $pocket = $this->getPocketImportService($pocketImport);

        $markAsRead = $this->session->get('mark_as_read');
        $this->session->remove('mark_as_read');

        // something bad happend on pocket side
        if (false === $pocket->authorize($this->session->get('import.pocket.code'))) {
            $this->addFlash('notice', $message);

            return $this->redirect($this->generateUrl('import_pocket'));
        }

        if (true === $pocket->setMarkAsRead($markAsRead)->import()) {
            $summary = $pocket->getSummary();
            $message = $translator->trans('flashes.import.notice.summary', [
                '%imported%' => null !== $summary && \array_key_exists('imported', $summary) ? $summary['imported'] : 0,
                '%skipped%' => null !== $summary && \array_key_exists('skipped', $summary) ? $summary['skipped'] : 0,
            ]);

            if (null !== $summary && \array_key_exists('queued', $summary) && 0 < $summary['queued']) {
                $message = $translator->trans('flashes.import.notice.summary_with_queue', [
                    '%queued%' => $summary['queued'],
                ]);
            }
        }

        $this->addFlash('notice', $message);

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Return Pocket Import Service with or without RabbitMQ enabled.
     */
    private function getPocketImportService(PocketImport $pocketImport): PocketImport
    {
        $pocketImport->setUser($this->getUser());

        if ($this->craueConfig->get('import_with_rabbitmq')) {
            $pocketImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $pocketImport->setProducer($this->redisProducer);
        }

        return $pocketImport;
    }
}
