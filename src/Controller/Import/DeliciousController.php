<?php

namespace App\Controller\Import;

use App\Form\Type\UploadImportType;
use App\Import\DeliciousImport;
use App\Redis\Producer as RedisProducer;
use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeliciousController extends AbstractController
{
    private RabbitMqProducer $rabbitMqProducer;
    private RedisProducer $redisProducer;

    public function __construct(RabbitMqProducer $rabbitMqProducer, RedisProducer $redisProducer)
    {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->redisProducer = $redisProducer;
    }

    /**
     * @Route("/import/delicious", name="import_delicious")
     */
    public function indexAction(Request $request, DeliciousImport $delicious, Config $craueConfig, TranslatorInterface $translator)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $delicious->setUser($this->getUser());

        if ($craueConfig->get('import_with_rabbitmq')) {
            $delicious->setProducer($this->rabbitMqProducer);
        } elseif ($craueConfig->get('import_with_redis')) {
            $delicious->setProducer($this->redisProducer);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $markAsRead = $form->get('mark_as_read')->getData();
            $name = 'delicious_' . $this->getUser()->getId() . '.json';

            if (null !== $file && \in_array($file->getClientMimeType(), $this->getParameter('wallabag.import.allow_mimetypes'), true) && $file->move($this->getParameter('wallabag.import.resource_dir'), $name)) {
                $res = $delicious
                    ->setFilepath($this->getParameter('wallabag.import.resource_dir') . '/' . $name)
                    ->setMarkAsRead($markAsRead)
                    ->import();

                $message = 'flashes.import.notice.failed';

                if (true === $res) {
                    $summary = $delicious->getSummary();
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

        return $this->render('Import/delicious.html.twig', [
            'form' => $form->createView(),
            'import' => $delicious,
        ]);
    }
}
