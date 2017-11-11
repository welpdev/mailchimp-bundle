<?php

namespace Welp\MailchimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Welp\MailchimpBundle\Provider\ListProviderInterface;

class SynchronizeSubscribersCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
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
     * @inheritDoc
     */
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
            
            $output->writeln(sprintf('Synchronize list %s', $list->getListId()));
            $batchesResult = $this->getContainer()->get('welp_mailchimp.list_synchronizer')->synchronize($list);
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
     * Refresh all batch from MailChimp API
     * @param array $batchesResult
     * @return array
     */
    private function refreshBatchesResult($batchesResult)
    {
        $refreshedBatchsResults = [];
        $mailchimp = $this->getContainer()->get('welp_mailchimp.mailchimp_master');
        foreach ($batchesResult as $key => $batch) {
            $batch = $mailchimp->get("batches/".$batch['id']);
            array_push($refreshedBatchsResults, $batch);
        }
        return $refreshedBatchsResults;
    }

    /**
     * Test if all batches are finished
     * @param array $batchesResult
     * @return bool
     */
    private function batchesFinished($batchesResult)
    {
        $allfinished = true;
        foreach ($batchesResult as $key => $batch) {
            if ($batch['status'] != 'finished') {
                $allfinished = false;
            }
        }
        return $allfinished;
    }

    /**
     * Pretty display of batch info
     * @param array $batch
     * @return string
     */
    private function displayBatchInfo($batch)
    {
        if ($batch['status'] == 'finished') {
            return sprintf('batch %s is finished, operations %d/%d with %d errors. http responses: %s', $batch['id'], $batch['finished_operations'], $batch['total_operations'], $batch['errored_operations'], $batch['response_body_url']);
        } else {
            return sprintf('batch %s, current status %s, operations %d/%d with %d errors', $batch['id'], $batch['status'], $batch['finished_operations'], $batch['total_operations'], $batch['errored_operations']);
        }
    }
}
