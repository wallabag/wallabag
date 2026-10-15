<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\Rss\FeedManager;
use Wallabag\Entity\User;

/** Operator-only local import; never invents accounts or authentication material. */
class RssImportCommand extends Command
{
    private EntityManagerInterface $em;
    private FeedManager $feeds;

    public function __construct(EntityManagerInterface $em, FeedManager $feeds)
    {
        $this->em = $em;
        $this->feeds = $feeds;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('wallabag:rss:import')->setDescription('Merge an offline legacy RSS export for a verified existing user.')
            ->addArgument('user-id', InputArgument::REQUIRED)
            ->addArgument('file', InputArgument::REQUIRED, 'JSON file or /dev/stdin; maximum 8 MiB.')
            ->addOption('enable-x-article-reader', null, InputOption::VALUE_NONE, 'Consent for this user to share public X post IDs/server IP with FxTwitter.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = filter_var($input->getArgument('user-id'), \FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $user = $id ? $this->em->getRepository(User::class)->find($id) : null;
        if (!$user || !$user->isEnabled()) {
            throw new \InvalidArgumentException('Choose a verified enabled existing user ID.');
        }
        $json = file_get_contents($input->getArgument('file'), false, null, 0, 8 * 1024 * 1024 + 1);
        $data = false !== $json && \strlen($json) <= 8 * 1024 * 1024 ? json_decode($json, true) : null;
        if (!\is_array($data)) {
            throw new \InvalidArgumentException('Expected JSON export up to 8 MiB.');
        }
        $result = $this->feeds->import($user, $data);
        if ($input->getOption('enable-x-article-reader')) {
            $user->getConfig()->setXArticleReaderEnabled(true);
            $this->em->flush();
        }
        $output->writeln(json_encode(['user_id' => $id, 'feeds' => $result['feeds'], 'x_article_reader_enabled' => $user->getConfig()->isXArticleReaderEnabled()]));

        return 0;
    }
}
