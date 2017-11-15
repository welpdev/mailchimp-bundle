<?php

namespace Welp\MailchimpBundle\Provider;

use Welp\MailchimpBundle\Provider\ListProviderInterface;
use Welp\MailchimpBundle\Subscriber\SubscriberList;

class ConfigListProvider implements ListProviderInterface
{
    private $providerFactory;
    private $lists;

    public function __construct(ProviderFactory $providerFactory, $lists)
    {
       $this->lists = $lists;
       $this->providerFactory = $providerFactory;
    }

    /**
     * Default list provider, retrieve the lists from the config file.
     * {@inheritdoc}
     */
    public function getLists()
    {
        if (sizeof($this->lists) == 0) {
            throw new \RuntimeException("No Mailchimp list has been defined. Check the your config.yml file based on MailchimpBundle's README.md");
        }
        $lists = array();
        foreach ($this->lists as $listId => $listParameters) 
        {            
            $lists[] = $this->createList($listId, $listParameters);            
        }
        return $lists;
    }

    /**
     * Default list provider, retrieve one list from the config file.
     * {@inheritdoc}
     */
    public function getList($listId)
    {
        foreach ($this->lists as $id => $listParameters) 
        {
            if($id == $listId)
            {
                return $this->createList($id, $listParameters);
            }
        }
    }
    
    /**
     * create one SubscriberList
     * @param string $listId
     * @param string $listParameters
     * @return SubscriberList 
     */
    private function createList($listId, $listParameters)
    {
        $providerServiceKey = $listParameters['subscriber_provider'];
        $provider = $this->providerFactory->create($providerServiceKey);
        $subscriberList = new SubscriberList($listId, $provider, $listParameters['merge_fields']);
        $subscriberList->setWebhookUrl($listParameters['webhook_url']);
        $subscriberList->setWebhookSecret($listParameters['webhook_secret']);

        return $subscriberList;
    }
}
