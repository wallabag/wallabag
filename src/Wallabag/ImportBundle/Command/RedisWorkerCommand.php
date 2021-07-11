<?php

namespace Wallabag\ImportBundle\Command;

use Simpleue\Worker\QueueWorker;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RedisWorkerCommand extends Command
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('wallabag:import:redis-worker')
            ->setDescription('Launch Redis worker')
            ->addArgument('serviceName', InputArgument::REQUIRED, 'Service to use: wallabag_v1, wallabag_v2, pocket, readability, pinboard, firefox, chrome or instapaper')
            ->addOption('maxIterations', '', InputOption::VALUE_OPTIONAL, 'Number of iterations before stoping', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Worker started at: ' . (new \DateTime())->format('d-m-Y G:i:s'));
        $output->writeln('Waiting for message ...');

        $serviceName = $input->getArgument('serviceName');

        if (!$this->container->has('wallabag_import.queue.redis.' . $serviceName) || !$this->container->has('wallabag_import.consumer.redis.' . $serviceName)) {
            throw new Exception(sprintf('No queue or consumer found for service name: "%s"', $input->getArgument('serviceName')));
        }

        $worker = new QueueWorker(
            $this->container->get('wallabag_import.queue.redis.' . $serviceName),
            $this->container->get('wallabag_import.consumer.redis.' . $serviceName),
            (int) $input->getOption('maxIterations')
        );

        $worker->start();
    }
}
