<?php

namespace Welp\MailchimpBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Welp\MailchimpBundle\Provider\ListProviderInterface;
use Welp\MailchimpBundle\Subscriber\ListSynchronizer;

class SynchronizeMergeFieldsCommand extends Command
{
    /**
     * List Synchronizer.
     *
     * @var ListSynchronizer
     */
    private ListSynchronizer $listSynchronizer;

    /**
     * The configured list provider.
     *
     * @var ListProviderInterface
     */
    private ListProviderInterface $listProvider;

    public function __construct(ListSynchronizer $listSynchronizer, ListProviderInterface $listProvider)
    {
        $this->listSynchronizer = $listSynchronizer;
        $this->listProvider = $listProvider;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronizing merge fields in MailChimp')
            ->setName('welp:mailchimp:synchronize-merge-fields')
            // @TODO add params : listId
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(sprintf('<info>%s</info>', $this->getDescription()));

        $lists = $this->listProvider->getLists();

        foreach ($lists as $list) {
            $this->listSynchronizer->synchronizeMergeFields($list->getListId(), $list->getMergeFields());
        }

        return Command::SUCCESS;
    }
}
