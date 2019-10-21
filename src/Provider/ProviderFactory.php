<?php

namespace Welp\MailchimpBundle\Provider;

class ProviderFactory
{
    /**
     * The available providers.
     *
     * @var array
     */
    private $providers;

    /**
     * Add a provider to the provider array.
     */
    public function addProvider(string $providerKey, ProviderInterface $provider): void
    {
        if (!isset($this->providers[$providerKey])) {
            $this->providers[$providerKey] = $provider;
        }
    }

    /**
     * Get subscriber provider.
     *
     * @param string $providerKey
     *
     * @return ProviderInterface $provider
     */
    public function create($providerKey)
    {
        if (!isset($this->providers[$providerKey])) {
            throw new \InvalidArgumentException(sprintf('Provider "%s" should be defined as a service.', $providerKey));
        }

        return $this->providers[$providerKey];
    }
}
