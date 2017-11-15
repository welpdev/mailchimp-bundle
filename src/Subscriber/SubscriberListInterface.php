<?php

namespace Welp\MailchimpBundle\Subscriber;

/**
 * SubscriberList interface linked a MailChimpList with a SubscriberProvider
 */
interface SubscriberListInterface
{
    /**
     * get the MailChimp ListId
     * @return string
     */
    public function getListId();

    /**
     * Get the subscriber provider
     * @return ProviderInterface
     */
    public function getProvider();

    /**
     * Get the list merge fields
     * @return array
     */
    public function getMergeFields();

    /**
     * Get the list webhook URL
     * @return string
     */
    public function getWebhookUrl();

    /**
     * Set the list webhook URL
     * @param string
     */
    public function setWebhookUrl($webhookUrl);

    /**
     * Get the list webhook secret
     * @return string
     */
    public function getWebhookSecret();

    /**
     * Set the list webhook secret
     * @param string
     */
    public function setWebhookSecret($webhookSecret);
}
