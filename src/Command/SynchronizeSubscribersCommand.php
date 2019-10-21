<?php

namespace Welp\MailchimpBundle\Command;

use DrewM\MailChimp\MailChimp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Welp\MailchimpBundle\Provider\ListProviderInterface;
use Welp\MailchimpBundle\Subscriber\ListSynchronizer;

class SynchronizeSubscribersCommand extends Command
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

    /**
     * Mailchimp API class.
     *
     * @var MailChimp
     */
    private $mailchimp;

    public function __construct(ListSynchronizer $listSynchronizer, ListProviderInterface $listProvider, MailChimp $mailchimp)
    {
        $this->listSynchronizer = $listSynchronizer;
        $this->listProvider = $listProvider;
        $this->mailchimp = $mailchimp;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Synchronizing subscribers in MailChimp')
            ->setName('welp:mailchimp:synchronize-subscribers')
            ->addOption(
                'follow-sync',
                null,
                InputOption::VALUE_NONE,
                'If you want to follow batches execution'
            )
            // @TODO add params : listId, providerServiceKey
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>%s</info>', $this->getDescription()));

        $lists = $this->listProvider->getLists();

        foreach ($lists as $list) {
            $output->writeln(sprintf('Synchronize list %s', $list->getListId()));
            $batchesResult = $this->listSynchronizer->synchronize($list);
            if ($input->getOption('follow-sync')) {
                while (!$this->batchesFinished($batchesResult)) {
                    $batchesResult = $this->refreshBatchesResult($batchesResult);
                    foreach ($batchesResult as $key => $batch) {
                        $output->writeln($this->displayBatchInfo($batch));
                    }
                    sleep(2);
                }
            }
        }
    }

    /**
     * Refresh all batch from MailChimp API.
     *
     * @param array $batchesResult
     *
     * @return array
     */
    private function refreshBatchesResult($batchesResult)
    {
        $refreshedBatchsResults = [];

        foreach ($batchesResult as $key => $batch) {
            $batch = $this->mailchimp->get('batches/'.$batch['id']);
            array_push($refreshedBatchsResults, $batch);
        }

        return $refreshedBatchsResults;
    }

    /**
     * Test if all batches are finished.
     *
     * @param array $batchesResult
     *
     * @return bool
     */
    private function batchesFinished($batchesResult)
    {
        $allfinished = true;
        foreach ($batchesResult as $key => $batch) {
            if ('finished' != $batch['status']) {
                $allfinished = false;
            }
        }

        return $allfinished;
    }

    /**
     * Pretty display of batch info.
     *
     * @param array $batch
     *
     * @return string
     */
    private function displayBatchInfo($batch)
    {
        if ('finished' == $batch['status']) {
            return sprintf('batch %s is finished, operations %d/%d with %d errors. http responses: %s', $batch['id'], $batch['finished_operations'], $batch['total_operations'], $batch['errored_operations'], $batch['response_body_url']);
        }

        return sprintf('batch %s, current status %s, operations %d/%d with %d errors', $batch['id'], $batch['status'], $batch['finished_operations'], $batch['total_operations'], $batch['errored_operations']);
    }
}
