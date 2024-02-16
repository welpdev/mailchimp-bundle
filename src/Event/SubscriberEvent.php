<?php

namespace Welp\MailchimpBundle\Event;

use Welp\MailchimpBundle\Subscriber\Subscriber;

/**
 * Event for User unit sync
 */
class SubscriberEvent
{
    /**
     * Event to subscribe a User
     * @var string
     */
    public const EVENT_SUBSCRIBE = 'welp.mailchimp.subscribe';

    /**
     * Event to unsubscribe a User
     * @var string
     */
    public const EVENT_UNSUBSCRIBE = 'welp.mailchimp.unsubscribe';

    /**
     * Event to pending a User
     * @var string
     */
    public const EVENT_PENDING = 'welp.mailchimp.pending';

    /**
     * Event to clean a User
     * @var string
     */
    public const EVENT_CLEAN = 'welp.mailchimp.clean';

    /**
     * Event to update a User
     * @var string
     */
    public const EVENT_UPDATE = 'welp.mailchimp.update';

    /**
     * Event to change email of a User
     * @var string
     */
    public const EVENT_CHANGE_EMAIL = 'welp.mailchimp.change_email';

    /**
     * Event to delete a User
     * @var string
     */
    public const EVENT_DELETE = 'welp.mailchimp.delete';

    /**
     * MailChimp ListId
     * @var string
     */
    protected string $listId;

    /**
     * User as Subscriber
     * @var Subscriber
     */
    protected Subscriber $subscriber;

    /**
     * Subscriber's oldEmail (used for EVENT_CHANGE_EMAIL)
     * @var string
     */
    protected ?string $oldEmail;

    /**
     *
     * @param string $listId
     * @param Subscriber $subscriber
     * @param string|null $oldEmail
     */
    public function __construct(string $listId, Subscriber $subscriber, ?string $oldEmail = null)
    {
        $this->listId = $listId;
        $this->subscriber = $subscriber;
        $this->oldEmail = $oldEmail;
    }

    /**
     * Get MailChimp listId
     * @return string
     */
    public function getListId(): string
    {
        return $this->listId;
    }

    /**
     * Get the User as Subscriber
     * @return Subscriber
     */
    public function getSubscriber(): Subscriber
    {
        return $this->subscriber;
    }

    /**
     * Get Subscriber's oldEmail (used for EVENT_CHANGE_EMAIL)
     * @return string|null
     */
    public function getOldEmail(): ?string
    {
        return $this->oldEmail;
    }
}
