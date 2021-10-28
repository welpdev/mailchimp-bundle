<?php

namespace Welp\MailchimpBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Welp\MailchimpBundle\Subscriber\Subscriber;

/**
 * Event for User unit sync
 */
class SubscriberEvent extends Event
{
    /**
     * Event to subscribe a User
     * @var string
     */
    const EVENT_SUBSCRIBE = 'welp.mailchimp.subscribe';

    /**
     * Event to unsubscribe a User
     * @var string
     */
    const EVENT_UNSUBSCRIBE = 'welp.mailchimp.unsubscribe';

    /**
     * Event to pending a User
     * @var string
     */
    const EVENT_PENDING = 'welp.mailchimp.pending';

    /**
     * Event to clean a User
     * @var string
     */
    const EVENT_CLEAN = 'welp.mailchimp.clean';

    /**
     * Event to update a User
     * @var string
     */
    const EVENT_UPDATE = 'welp.mailchimp.update';

    /**
     * Event to change email of a User
     * @var string
     */
    const EVENT_CHANGE_EMAIL = 'welp.mailchimp.change_email';

    /**
     * Event to delete a User
     * @var string
     */
    const EVENT_DELETE = 'welp.mailchimp.delete';

    /**
     * MailChimp ListId
     * @var string
     */
    protected $listId;

    /**
     * User as Subscriber
     * @var Subscriber
     */
    protected $subscriber;

    /**
     * Subscriber's oldEmail (used for EVENT_CHANGE_EMAIL)
     * @var string
     */
    protected $oldEmail;

    /**
     *
     * @param string     $listId
     * @param Subscriber $subscriber
     * @param string     $oldEmail
     */
    public function __construct($listId, Subscriber $subscriber, $oldEmail = null)
    {
        $this->listId = $listId;
        $this->subscriber = $subscriber;
        $this->oldEmail = $oldEmail;
    }

    /**
     * Get MailChimp listId
     * @return string
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * Get the User as Subscriber
     * @return Subscriber
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * Get Subscriber's oldEmail (used for EVENT_CHANGE_EMAIL)
     * @return string
     */
    public function getOldEmail()
    {
        return $this->oldEmail;
    }
}
