<?php

namespace Welp\MailchimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

use Welp\MailchimpBundle\Provider\ProviderInterface;
use Welp\MailchimpBundle\Subscriber\SubscriberList;

class SynchronizeSubscribersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Synchronizing subscribers in MailChimp')
            ->setName('welp:mailchimp:synchronize-subscribers')
        ;
    }

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

            $this->getContainer()->get('welp_mailchimp.list_synchronizer')->synchronize($list);
        }
    }

    /**
     * Get subscriber provider
     * @param String $providerServiceKey
     * @return ProviderInterface $provider
     */
    protected function getProvider($providerServiceKey)
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
