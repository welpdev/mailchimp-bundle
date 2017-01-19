<?php

namespace Welp\MailchimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

use Welp\MailchimpBundle\Provider\ProviderInterface;
use Welp\MailchimpBundle\Subscriber\SubscriberList;

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

        $lists = $this->getContainer()->getParameter('welp_mailchimp.lists');
        if (sizeof($lists) == 0) {
            throw new \RuntimeException("No Mailchimp list has been defined. Check the your config.yml file based on MailchimpBundle's README.md");
        }

        foreach ($lists as $listId => $listParameters) {
            $providerServiceKey = $listParameters['subscriber_provider'];

            $provider = $this->getProvider($providerServiceKey);
            $list = new SubscriberList($listId, $provider);

            $output->writeln(sprintf('Synchronize list %s', $listId));
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

    /**
     * Get subscriber provider
     * @param string $providerServiceKey
     * @return ProviderInterface $provider
     */
    private function getProvider($providerServiceKey)
    {
        try {
            $provider = $this->getContainer()->get($providerServiceKey);
        } catch (ServiceNotFoundException $e) {
            throw new \InvalidArgumentException(sprintf('Provider "%s" should be defined as a service.', $providerServiceKey), $e->getCode(), $e);
        }

        if (!$provider instanceof ProviderInterface) {
            throw new \InvalidArgumentException(sprintf('Provider "%s" should implement Welp\MailchimpBundle\Provider\ProviderInterface.', $providerServiceKey));
        }

        return $provider;
    }
}
