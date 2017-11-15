<?php

namespace Welp\MailchimpBundle\Provider;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Welp\MailchimpBundle\Provider\ProviderInterface;

class ProviderFactory {

    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * Get subscriber provider
     * @param string $providerServiceKey
     * @return ProviderInterface $provider
     */
    public function create($providerServiceKey) 
    {
        try {
            $provider = $this->container->get($providerServiceKey);
        } catch (ServiceNotFoundException $e) {
            throw new \InvalidArgumentException(sprintf('Provider "%s" should be defined as a service.', $providerServiceKey), $e->getCode(), $e);
        }

        if (!$provider instanceof ProviderInterface) {
            throw new \InvalidArgumentException(sprintf('Provider "%s" should implement Welp\MailchimpBundle\Provider\ProviderInterface.', $providerServiceKey));
        }

        return $provider;
    }
}