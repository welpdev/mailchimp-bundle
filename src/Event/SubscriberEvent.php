<?php

namespace Welp\MailchimpBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Welp\MailchimpBundle\Subscriber\Subscriber;

class SubscriberEvent extends Event
{
    const EVENT_SUBSCRIBE = 'welp.mailchimp.subscribe';
    const EVENT_UNSUBSCRIBE = 'welp.mailchimp.unsubscribe';

    protected $listId;
    protected $subscriber;

    public function __construct($listId, Subscriber $subscriber)
    {
        $this->listId = $listId;
        $this->subscriber = $subscriber;
    }

    public function getListId()
    {
        return $this->listId;
    }

    public function getSubscriber()
    {
        return $this->subscriber;
    }
}
