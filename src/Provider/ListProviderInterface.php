<?php

namespace Welp\MailchimpBundle\Provider;

/**
 * Subscriber provider interface
 */
interface ListProviderInterface
{
    /**
     * Get all the available Mailchimp lists
     * @return array of SubscriberList
     */
    public function getLists();

    /**
     * Get one Mailchimp list by id
     * @return SubscriberList
     */
    public function getList($listId);
}
