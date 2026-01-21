<?php

namespace Welp\MailchimpBundle\Subscriber;

use Welp\MailchimpBundle\Provider\ProviderInterface;

/**
 * SubscriberList interface linked a MailChimpList with a SubscriberProvider
 */
interface SubscriberListInterface
{
    /**
     * get the MailChimp ListId
     * @return string
     */
    public function getListId(): string;

    /**
     * Get the subscriber provider
     * @return ProviderInterface
     */
    public function getProvider(): ProviderInterface;

    /**
     * Get the list merge fields
     * @return array
     */
    public function getMergeFields(): array;

    /**
     * Get the list webhook URL
     * @return string
     */
    public function getWebhookUrl(): string;

    /**
     * Set the list webhook URL
     * @param string $webhookUrl
     */
    public function setWebhookUrl(string $webhookUrl);

    /**
     * Get the list webhook secret
     * @return string
     */
    public function getWebhookSecret(): string;

    /**
     * Set the list webhook secret
     * @param string $webhookSecret
     */
    public function setWebhookSecret(string $webhookSecret);
}
