<?php

namespace Welp\MailchimpBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event for MailChimp webhook
 */
class WebhookEvent extends Event
{
    /**
     * Event triggered when received webhook for user subscribe
     * @var string
     */
    const EVENT_SUBSCRIBE = 'welp.mailchimp.webhook.subscribe';
    /**
     * Event triggered when received webhook for user unsubscribe
     * @var string
     */
    const EVENT_UNSUBSCRIBE = 'welp.mailchimp.webhook.unsubscribe';
    /**
     * Event triggered when received webhook for user update profile
     * @var string
     */
    const EVENT_PROFILE = 'welp.mailchimp.webhook.profile';
    /**
     * Event triggered when received webhook for user cleaned
     * @var string
     */
    const EVENT_CLEANED = 'welp.mailchimp.webhook.cleaned';
    /**
     * Event triggered when received webhook for user update email [legacy?]
     * @var string
     */
    const EVENT_UPEMAIL = 'welp.mailchimp.webhook.upemail';
    /**
     * Event triggered when received webhook for new campaign send
     * @var string
     */
    const EVENT_CAMPAIGN = 'welp.mailchimp.webhook.campaign';

    /**
     * Data form webhook request
     * @var array
     */
    protected $data;

    /**
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get data form webhook request
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
