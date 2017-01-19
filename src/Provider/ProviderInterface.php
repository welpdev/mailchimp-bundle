<?php

namespace Welp\MailchimpBundle\Provider;

/**
 * Subscriber provider interface
 */
interface ProviderInterface
{
    /**
     * Get your AppUser to be formatted into Subscribers
     * in order to sync with MailChimp List
     * @return array of Subscriber
     */
    public function getSubscribers();
}
