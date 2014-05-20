<?php
namespace Wallabag\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setName('tests:unit')
            ->setDescription('Launches units tests with Atoum')
            ->addOption('loop', null, InputOption::VALUE_NONE, 'Execute tests in an infinite loop')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $atoum = $this->getProjectDirectory().'/vendor/bin/atoum';
        $unitTests = $this->getProjectDirectory().'/tests/units/';
        $bootstrapFile = $this->getProjectDirectory().'/tests/units/bootstrap.php';
        $command = '%s -d %s -bf %s -ft';

        if ($input->getOption('loop')) {
            $command .= ' -l';
        }
        passthru(sprintf($command, $atoum, $unitTests, $bootstrapFile), $status);

        return $status;
    }
}
