<?php

namespace Welp\MailchimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Welp\MailchimpBundle\Provider\ListProviderInterface;

class SynchronizeMergeFieldsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Synchronizing merge fields in MailChimp')
            ->setName('welp:mailchimp:synchronize-merge-fields')
            // @TODO add params : listId
        ;
    } 

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>%s</info>', $this->getDescription()));

        $listProviderKey = $this->getContainer()->getParameter('welp_mailchimp.list_provider');
        try {
            $listProvider = $this->getContainer()->get($listProviderKey); 
        } catch (ServiceNotFoundException $e) {
            throw new \InvalidArgumentException(sprintf('List Provider "%s" should be defined as a service.', $listProviderKey), $e->getCode(), $e);
        }

        if (!$listProvider instanceof ListProviderInterface) {
            throw new \InvalidArgumentException(sprintf('List Provider "%s" should implement Welp\MailchimpBundle\Provider\ListProviderInterface.', $listProviderKey));
        }

        $lists = $listProvider->getLists();

        foreach ($lists as $list) {
            $this->getContainer()->get('welp_mailchimp.list_synchronizer')
                ->synchronizeMergeFields($list->getListId(), $list->getMergeFields());
            ;
        }
    }
}
