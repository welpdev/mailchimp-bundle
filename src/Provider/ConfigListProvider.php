<?php

namespace Welp\MailchimpBundle\Provider;

use Welp\MailchimpBundle\Subscriber\SubscriberList;
use Welp\MailchimpBundle\Subscriber\SubscriberListInterface;

class ConfigListProvider implements ListProviderInterface
{
    private ProviderFactory $providerFactory;
    private array $lists;

    public function __construct(ProviderFactory $providerFactory, $lists)
    {
       $this->lists = $lists;
       $this->providerFactory = $providerFactory;
    }

    /**
     * Default list provider, retrieve the lists from the config file.
     * {@inheritdoc}
     */
    public function getLists(): array
    {
        if (count($this->lists) === 0) {
            throw new \RuntimeException('No Mailchimp list has been defined. Check the your config.yml file based on MailchimpBundle\'s README.md');
        }

        $lists = [];

        foreach ($this->lists as $listId => $listParameters) {
            $lists[] = $this->createList($listId, $listParameters);            
        }

        return $lists;
    }

    /**
     * Default list provider, retrieve one list from the config file.
     * {@inheritdoc}
     */
    public function getList($listId): SubscriberListInterface
    {
        foreach ($this->lists as $id => $listParameters) {
            if ($id === $listId) {
                return $this->createList($id, $listParameters);
            }
        }

        throw new \RuntimeException('Selected Mailchimp list has not been found. Check the your config.yml file based on MailchimpBundle\'s README.md');
    }
    
    /**
     * create one SubscriberList
     * @param string $listId
     * @param array  $listParameters
     * @return SubscriberList 
     */
    private function createList(string $listId, array $listParameters): SubscriberList
    {
        $providerServiceKey = $listParameters['subscriber_provider'];
        $provider = $this->providerFactory->create($providerServiceKey);
        $subscriberList = new SubscriberList($listId, $provider, $listParameters['merge_fields']);
        $subscriberList->setWebhookUrl($listParameters['webhook_url']);
        $subscriberList->setWebhookSecret($listParameters['webhook_secret']);

        return $subscriberList;
    }
}
