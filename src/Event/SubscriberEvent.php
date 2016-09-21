<?php

namespace Welp\MailchimpBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Welp\MailchimpBundle\Subscriber\Subscriber;

class SubscriberEvent extends Event
{
    const EVENT_SUBSCRIBE = 'welp.mailchimp.subscribe';
    const EVENT_UNSUBSCRIBE = 'welp.mailchimp.unsubscribe';
    const EVENT_UPDATE = 'welp.mailchimp.update';
    const EVENT_CHANGE_EMAIL = 'welp.mailchimp.change_email';
    const EVENT_DELETE = 'welp.mailchimp.delete';

    protected $listId;
    protected $subscriber;
    protected $oldEmail;

    public function __construct($listId, Subscriber $subscriber, $oldEmail = null)
    {
        $this->listId = $listId;
        $this->subscriber = $subscriber;
        $this->oldEmail = $oldEmail;
    }

    public function getListId()
    {
        return $this->listId;
    }

    public function getSubscriber()
    {
        return $this->subscriber;
    }

    public function getOldEmail()
    {
        return $this->oldEmail;
    }
}
