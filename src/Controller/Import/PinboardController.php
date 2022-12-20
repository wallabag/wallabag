<?php

namespace App\Controller\Import;

use App\Form\Type\UploadImportType;
use App\Import\PinboardImport;
use App\Redis\Producer as RedisProducer;
use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PinboardController extends AbstractController
{
    private RabbitMqProducer $rabbitMqProducer;
    private RedisProducer $redisProducer;

    public function __construct(RabbitMqProducer $rabbitMqProducer, RedisProducer $redisProducer)
    {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->redisProducer = $redisProducer;
    }

    /**
     * @Route("/import/pinboard", name="import_pinboard")
     */
    public function indexAction(Request $request, PinboardImport $pinboard, Config $craueConfig, TranslatorInterface $translator)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $pinboard->setUser($this->getUser());

        if ($craueConfig->get('import_with_rabbitmq')) {
            $pinboard->setProducer($this->rabbitMqProducer);
        } elseif ($craueConfig->get('import_with_redis')) {
            $pinboard->setProducer($this->redisProducer);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $markAsRead = $form->get('mark_as_read')->getData();
            $name = 'pinboard_' . $this->getUser()->getId() . '.json';

            if (null !== $file && \in_array($file->getClientMimeType(), $this->getParameter('wallabag.import.allow_mimetypes'), true) && $file->move($this->getParameter('wallabag.import.resource_dir'), $name)) {
                $res = $pinboard
                    ->setFilepath($this->getParameter('wallabag.import.resource_dir') . '/' . $name)
                    ->setMarkAsRead($markAsRead)
                    ->import();

                $message = 'flashes.import.notice.failed';

                if (true === $res) {
                    $summary = $pinboard->getSummary();
                    $message = $translator->trans('flashes.import.notice.summary', [
                        '%imported%' => $summary['imported'],
                        '%skipped%' => $summary['skipped'],
                    ]);

                    if (0 < $summary['queued']) {
                        $message = $translator->trans('flashes.import.notice.summary_with_queue', [
                            '%queued%' => $summary['queued'],
                        ]);
                    }

                    unlink($this->getParameter('wallabag.import.resource_dir') . '/' . $name);
                }

                $this->addFlash('notice', $message);

                return $this->redirect($this->generateUrl('homepage'));
            }

            $this->addFlash('notice', 'flashes.import.notice.failed_on_file');
        }

        return $this->render('Import/pinboard.html.twig', [
            'form' => $form->createView(),
            'import' => $pinboard,
        ]);
    }
}
