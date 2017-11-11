<?php

namespace Welp\MailchimpBundle\Provider;

/**
 * Dynamic Subscriber provider interface
 * Can be used for multiple lists
 */
interface DynamicProviderInterface extends ProviderInterface
{

    /**
     * Set the listId
     * @param  string $listId
     * @return void
     */
    public function setListId(string $listId);
}
