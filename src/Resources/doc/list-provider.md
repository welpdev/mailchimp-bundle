# List Provider

By default the list configuration is read from the `config.yml` when correctly defined according to [configuring your lists](configuration.md). This is done by the default list provider (`ConfigListProvider`). If you want to use a diffrent source for your list config you can create your own List Provider that should implement `Welp\MailChimpBundle\Provider\ListProviderInterface`.

```php
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
```

With your own implementation you could for example fetch your list configuration from Doctrine. 
Example implementation:

```php
<?php

namespace YourApp\App\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Welp\MailchimpBundle\Provider\ListProviderInterface;
use Welp\MailchimpBundle\Subscriber\SubscriberListInterface;

class DoctrineListProvider implements ListProviderInterface
{

    private $em;
    private $listEntity;
    private $userProvider;

    public function __construct(EntityManagerInterface $entityManager, SubscriberListInterface $listEntity, $userProvider)
    {
        $this->em = $entityManager;
        $this->listEntity = $listEntity;
        $this->userProvider = $userProvider;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getList($listId)
    {
        $list = $this->em->getRepository($this->listEntity)->findOneByListId($listId);
        $list->setProvider($this->userProvider);
        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $lists = $this->em->getRepository($this->listEntity)->findAll();
        foreach($lists as $list)
        {
            //add the provider to the list
            $list->setProvider($this->userProvider);
        }
        return $lists;
    }   
}

```

Define your List provider as a service:

```yaml
doctrine.list.provider:
    public: true
    arguments:
        - '@doctrine.orm.entity_manager'
        - 'Welp\MailchimpBundle\Subscriber\SubscriberList'
        - '@example_user_provider'
```

After this, don't forget to add the service key for your provider into your `config.yml`:

```yaml
    welp_mailchimp:
        api_key: ...
        list_provider: 'doctrine.list.provider'
        ...
```
