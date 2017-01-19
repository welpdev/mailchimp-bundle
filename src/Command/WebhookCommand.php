<?php

namespace Welp\MailchimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        $lists = $this->getContainer()->getParameter('welp_mailchimp.lists');
        if (sizeof($lists) == 0) {
            throw new \RuntimeException("No Mailchimp list has been defined. Check the your config.yml file based on MailchimpBundle's README.md");
        }

        foreach ($lists as $listId => $listParameters) {
            $url = $listParameters['webhook_url'].'?hooksecret='.$listParameters['webhook_secret'];
            $output->writeln('Add webhook to list: '.$listId);
            $output->writeln('Webhook url: '.$url);

            $this->getContainer()->get('welp_mailchimp.list_repository')
                ->registerMainWebhook($listId, $url);
            ;
        }

        $output->writeln('✔ done');
    }
}
