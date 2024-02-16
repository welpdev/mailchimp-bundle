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
    protected string $listId;

    /**
     * Subscriber provider
     * @var ProviderInterface
     */
    protected ProviderInterface $provider;

    /**
     * Merge fields
     * @var array
     */
    protected array $mergeFields;

    /**
     * MailChimp webhook URL
     * @var string
     */
    protected string $webhookUrl = '';

    /**
     * MailChimp webhook secret
     * @var string
     */
    protected string $webhookSecret = '';

    /**
     *
     * @param string $listId
     * @param ProviderInterface $provider
     * @param array             $mergeFields
     */
    public function __construct(string $listId, ProviderInterface $provider, array $mergeFields = [])
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
    public function getListId(): string
    {
        return $this->listId;
    }

    /**
     * Get the subscriber provider
     * @return ProviderInterface
     */
    public function getProvider(): ProviderInterface
    {
        return $this->provider;
    }

    /**
     * Get the list merge fields
     * @return array
     */
    public function getMergeFields(): array
    {
        return $this->mergeFields;
    }

    /**
     * Get the list webhook URL
     * @return string
     */
    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }

    /**
     * Set the list webhook URL
     * @param string $webhookUrl
     */
    public function setWebhookUrl(string $webhookUrl): void
    {
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * Get the list webhook secret
     * @return string
     */
    public function getWebhookSecret(): string
    {
        return $this->webhookSecret;
    }

    /**
     * Set the list webhook secret
     * @param string $webhookSecret
     */
    public function setWebhookSecret(string $webhookSecret): void
    {
        $this->webhookSecret = $webhookSecret;
    }
}
