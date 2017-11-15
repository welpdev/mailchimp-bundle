<?php

namespace Welp\MailchimpBundle\Subscriber;

use Welp\MailchimpBundle\Provider\ProviderInterface;
use Welp\MailchimpBundle\Provider\DynamicProviderInterface;

/**
 * SubscriberList linked a MailChimpList with a SubscriberProvider
 */
class SubscriberList implements SubscriberListInterface
{
    /**
     * MailChimp ListId
     * @var string
     */
    protected $listId;

    /**
     * Subscriber provider
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * Merge fields
     * @var array
     */
    protected $mergeFields;

    /**
     * MailChimp webhook URL
     * @var string
     */
    protected $webhookUrl;
    
    /**
     * MailChimp webhook secret
     * @var string
     */
    protected $webhookSecret;

    /**
     *
     * @param string            $listId
     * @param ProviderInterface $provider
     * @param array             $mergeFields
     */
    public function __construct($listId, ProviderInterface $provider, array $mergeFields = array())
    {
        $this->listId = $listId;
        $this->provider = $provider;
        $this->mergeFields = $mergeFields;

        //If the provider implements DynamicProviderInterface, set the list id in the provider
        if ($this->provider instanceof DynamicProviderInterface) {
            $this->provider->setListId($this->listId);
        }        
    }

    /**
     * get the MailChimp ListId
     * @return string
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * Get the subscriber provider
     * @return ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Get the list merge fields
     * @return array
     */
    public function getMergeFields()
    {
        return $this->mergeFields;
    }

    /**
     * Get the list webhook URL
     * @return string
     */
    public function getWebhookUrl()
    {
        return $this->webhookUrl;
    }

    /**
     * Set the list webhook URL
     * @param string
     */
    public function setWebhookUrl($webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * Get the list webhook secret
     * @return string
     */
    public function getWebhookSecret()
    {
        return $this->webhookSecret;
    }

    /**
     * Set the list webhook secret
     * @param string
     */
    public function setWebhookSecret($webhookSecret)
    {
        $this->webhookSecret = $webhookSecret;
    }
}
