<?php

namespace Welp\MailchimpBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Welp\MailchimpBundle\Subscriber\Subscriber;

class SubscriberEvent extends Event
{
    const EVENT_SUBSCRIBE = 'welp.mailchimp.subscribe';
    const EVENT_UNSUBSCRIBE = 'welp.mailchimp.unsubscribe';

    protected $listname;
    protected $subscriber;

    public function __construct($listname, Subscriber $subscriber)
    {
        $this->listname = $listname;
        $this->subscriber = $subscriber;
    }

    public function getListName()
    {
        return $this->listname;
    }

    public function getSubscriber()
    {
        return $this->subscriber;
    }
}
