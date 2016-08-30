<?php

namespace Welp\MailchimpBundle\Subscriber;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Welp\MailchimpBundle\Provider\ProviderInterface;

class SubscriberList
{
    protected $listId;
    protected $provider;
    protected $options;

    public function __construct($listId, ProviderInterface $provider, array $options = [])
    {
        $this->listId = $listId;
        $this->provider = $provider;
        $this->options = $this->resolveOptions($options);
    }

    public function getListId()
    {
        return $this->listId;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getOptions()
    {
        return $this->options;
    }

    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['mc_language' => null]);

        return $resolver->resolve($options);
    }
}
