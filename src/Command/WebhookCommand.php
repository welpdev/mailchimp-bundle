<?php

namespace Welp\MailchimpBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Welp\MailchimpBundle\Provider\ListProviderInterface;
use Welp\MailchimpBundle\Subscriber\ListRepository;

class WebhookCommand extends Command
{
    /**
     * The configured list provider.
     *
     * @var ListProviderInterface
     */
    private $listProvider;

    /**
     * The configured list repository.
     *
     * @var ListRepository
     */
    private $listRepository;

    public function __construct(ListProviderInterface $listProvider, ListRepository $listRepository)
    {
        $this->listProvider = $listProvider;
        $this->listRepository = $listRepository;

        parent::__construct();
    }

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

        $lists = $this->listProvider->getLists();

        foreach ($lists as $list) {
            $url = $list->getWebhookUrl().'?hooksecret='.$list->getWebhookSecret();
            $output->writeln('Add webhook to list: '.$list->getListId());
            $output->writeln('Webhook url: '.$url);

            $this->listRepository->registerMainWebhook($list->getListId(), $url);
        }

        $output->writeln('✔ done');
    }
}
