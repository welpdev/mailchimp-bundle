<?php

namespace Welp\MailchimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Monolog\Logger;
use Welp\MailchimpBundle\Provider\ProviderInterface;
use Welp\MailchimpBundle\Subscriber\SubscriberList;

class SynchronizeMergeTagsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Synchronizing merge tags in MailChimp')
            ->setName('welp:mailchimp:synchronize-merge-tags')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>%s</info>', $this->getDescription()));

        $this->getContainer()->get('logger')->pushHandler(new ConsoleHandler($output, true, array(
            OutputInterface::VERBOSITY_NORMAL => Logger::INFO,
            OutputInterface::VERBOSITY_VERBOSE => Logger::DEBUG,
            OutputInterface::VERBOSITY_VERY_VERBOSE => Logger::DEBUG,
            OutputInterface::VERBOSITY_DEBUG => Logger::DEBUG,
        )));

        $lists = $this->getContainer()->getParameter('welp_mailchimp.lists');
        if (sizeof($lists) == 0) {
            throw new \RuntimeException("No Mailchimp list has been defined. Check the your config.yml file based on MailchimpBundle's README.md");
        }

        foreach ($lists as $listName => $listParameters) {
            $this->getContainer()->get('welp_mailchimp.list_synchronizer')
                ->synchronizeMergeTags($listName, $listParameters['merge_tags']);
            ;
        }
    }
}
