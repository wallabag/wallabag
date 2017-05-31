<?php

namespace Wallabag\FederationBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\FederationBundle\Entity\Account;
use Wallabag\FederationBundle\Entity\Instance;
use Wallabag\UserBundle\Entity\User;

class CreateAccountsCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    protected $output;

    protected $created = 0;

    protected function configure()
    {
        $this
            ->setName('wallabag:federation:create-accounts')
            ->setDescription('Creates missing federation accounts')
            ->setHelp('This command creates accounts for federation for missing users')
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                'User to create an account for'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $domainName = $this->getContainer()->getParameter('domain_name');
        $instance = $this->checkInstance($domainName);

        $username = $input->getArgument('username');

        if ($username) {
            try {
                $user = $this->getUser($username);
                $this->createAccount($user, $instance);
            } catch (NoResultException $e) {
                $output->writeln(sprintf('<error>User "%s" not found.</error>', $username));

                return 1;
            }
        } else {
            $users = $this->getDoctrine()->getRepository('WallabagUserBundle:User')->findAll();

            $output->writeln(sprintf('Creating through %d user federated accounts', count($users)));

            foreach ($users as $user) {
                $output->writeln(sprintf('Processing user %s', $user->getUsername()));
                $this->createAccount($user, $instance);
            }
            $output->writeln(sprintf('Creating user federated accounts. %d accounts created in total', $this->created));
        }

        return 0;
    }

    /**
     * @param User $user
     * @param $instance
     */
    private function createAccount(User $user, Instance $instance)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WallabagFederationBundle:Account');

        if ($repo->findBy(['user' => $user->getId()])) {
            return;
        }

        $account = new Account();
        $account->setUsername($user->getUsername())
            ->setUser($user)
            ->setServer($instance);

        $em->persist($account);
        $em->flush();

        $user->setAccount($account);
        $em->persist($account);
        $em->flush();

        ++$this->created;
    }

    private function checkInstance($domainName)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WallabagFederationBundle:Instance');

        $instance = $repo->findOneByDomain($domainName);
        if (!$instance) {
            $instance = new Instance($domainName);

            $em->persist($instance);
            $em->flush();
        }
        return $instance;
    }

    /**
     * Fetches a user from its username.
     *
     * @param string $username
     *
     * @return \Wallabag\UserBundle\Entity\User
     */
    private function getUser($username)
    {
        return $this->getDoctrine()->getRepository('WallabagUserBundle:User')->findOneByUserName($username);
    }

    private function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }
}
