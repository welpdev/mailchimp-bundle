<?php

namespace Welp\MailchimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Welp\MailchimpBundle\Provider\ListProviderInterface;

class WebhookCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Add main webhook to a MailChimp List')
            ->setName('welp:mailchimp:webhook')
        ;
        // @TODO add params : listId, webhookurl
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
            $url = $list->getWebhookUrl().'?hooksecret='.$list->getWebhookSecret();
            $output->writeln('Add webhook to list: '.$list->getListId());
            $output->writeln('Webhook url: '.$url);

            $this->getContainer()->get('welp_mailchimp.list_repository')
                ->registerMainWebhook($list->getListId(), $url);
            ;
        }

        $output->writeln('✔ done');
    }
}
