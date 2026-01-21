<?php

namespace Welp\MailchimpBundle\Provider;

use Welp\MailchimpBundle\Subscriber\SubscriberListInterface;

/**
 * List provider interface
 */
interface ListProviderInterface
{
    /**
     * Get all the available Mailchimp lists
     * @return array of SubscriberListInterface
     */
    public function getLists(): array;

    /**
     * Get one Mailchimp list by id
     * @param $listId
     * @return SubscriberListInterface
     */
    public function getList($listId): SubscriberListInterface;
}
