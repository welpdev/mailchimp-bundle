<?php

namespace Welp\MailchimpBundle\Subscriber;

use Welp\MailchimpBundle\Provider\ProviderInterface;
use Welp\MailchimpBundle\Provider\DynamicProviderInterface;

/**
 * SubscriberList linked a MailChimpList with a SubscriberProvider
 */
class SubscriberList
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
}
