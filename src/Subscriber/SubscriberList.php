<?php

namespace Welp\MailchimpBundle\Subscriber;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Welp\MailchimpBundle\Provider\ProviderInterface;

class SubscriberList
{
    protected $listId;
    protected $provider;

    public function __construct($listId, ProviderInterface $provider)
    {
        $this->listId = $listId;
        $this->provider = $provider;
    }

    public function getListId()
    {
        return $this->listId;
    }

    public function getProvider()
    {
        return $this->provider;
    }
}
