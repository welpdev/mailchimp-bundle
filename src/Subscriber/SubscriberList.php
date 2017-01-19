<?php

namespace Welp\MailchimpBundle\Subscriber;

use Welp\MailchimpBundle\Provider\ProviderInterface;

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
     *
     * @param string            $listId
     * @param ProviderInterface $provider
     */
    public function __construct($listId, ProviderInterface $provider)
    {
        $this->listId = $listId;
        $this->provider = $provider;
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
}
