<?php

namespace Welp\MailchimpBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Welp\MailchimpBundle\Subscriber\Subscriber;

class WebhookEvent extends Event
{
    const EVENT_SUBSCRIBE = 'welp.mailchimp.webhook.subscribe';
    const EVENT_UNSUBSCRIBE = 'welp.mailchimp.webhook.unsubscribe';
    const EVENT_PROFILE = 'welp.mailchimp.webhook.profile';
    const EVENT_CLEANED = 'welp.mailchimp.webhook.cleaned';
    const EVENT_UPEMAIL = 'welp.mailchimp.webhook.upemail';
    const EVENT_CAMPAIGN = 'welp.mailchimp.webhook.campaign';

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
