<?php

namespace Welp\MailchimpBundle\Subscriber;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Welp\MailchimpBundle\Provider\ProviderInterface;

class SubscriberList
{
    protected $name;
    protected $provider;
    protected $options;

    public function __construct($name, ProviderInterface $provider, array $options = [])
    {
        $this->name = $name;
        $this->provider = $provider;
        $this->options = $this->resolveOptions($options);
    }

    public function getName()
    {
        return $this->name;
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
