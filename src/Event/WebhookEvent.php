<?php

namespace Welp\MailchimpBundle\Event;

/**
 * Event for MailChimp webhook
 */
class WebhookEvent
{
    /**
     * Event triggered when received webhook for user subscribe
     * @var string
     */
    public const EVENT_SUBSCRIBE = 'welp.mailchimp.webhook.subscribe';
    /**
     * Event triggered when received webhook for user unsubscribe
     * @var string
     */
    public const EVENT_UNSUBSCRIBE = 'welp.mailchimp.webhook.unsubscribe';
    /**
     * Event triggered when received webhook for user update profile
     * @var string
     */
    public const EVENT_PROFILE = 'welp.mailchimp.webhook.profile';
    /**
     * Event triggered when received webhook for user cleaned
     * @var string
     */
    public const EVENT_CLEANED = 'welp.mailchimp.webhook.cleaned';
    /**
     * Event triggered when received webhook for user update email [legacy?]
     * @var string
     */
    public const EVENT_UPEMAIL = 'welp.mailchimp.webhook.upemail';
    /**
     * Event triggered when received webhook for new campaign send
     * @var string
     */
    public const EVENT_CAMPAIGN = 'welp.mailchimp.webhook.campaign';

    /**
     * Data form webhook request
     * @var array
     */
    protected array $data;

    /**
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get data form webhook request
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
