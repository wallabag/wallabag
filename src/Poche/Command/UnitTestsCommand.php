<?php
namespace Poche\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Knp\Command\Command as BaseCommand;

/**
 * Application aware command
 *
 * Provide a silex application in CLI context.
 */
class UnitTestsCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('tests:units')
            ->setDescription('Launches units tests with Atoum')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
