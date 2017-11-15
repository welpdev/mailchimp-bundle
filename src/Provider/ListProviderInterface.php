<?php

namespace Welp\MailchimpBundle\Provider;

/**
 * List provider interface
 */
interface ListProviderInterface
{
    /**
     * Get all the available Mailchimp lists
     * @return array of SubscriberListInterface
     */
    public function getLists();

    /**
     * Get one Mailchimp list by id
     * @return SubscriberListInterface
     */
    public function getList($listId);
}
