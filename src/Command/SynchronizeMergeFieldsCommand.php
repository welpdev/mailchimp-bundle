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
    private $listSynchronizer;

    /**
     * The configured list provider.
     *
     * @var ListProviderInterface
     */
    private $listProvider;

    public function __construct(ListSynchronizer $listSynchronizer, ListProviderInterface $listProvider)
    {
        $this->listSynchronizer = $listSynchronizer;
        $this->listProvider = $listProvider;

        parent::__construct();
    }

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

        $lists = $this->listProvider->getLists();

        foreach ($lists as $list) {
            $this->listSynchronizer->synchronizeMergeFields($list->getListId(), $list->getMergeFields());
        }
    }
}
