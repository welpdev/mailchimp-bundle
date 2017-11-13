# Subscriber Provider

After [configuring your lists](configuration.md) in `config.yml`, you need to create at least one `Provider` that will be used by the Symfony command. Your provider should be accessible via a service key (the same you reference in `subscriber_provider` in the configuration above):

```yaml
services:
    yourapp_mailchimp_subscriber_provider:
        class: YourApp\App\Newsletter\SubscriberProvider
        arguments: [@yourapp_user_repository]
```

It should implement `Welp\MailChimpBundle\Provider\ProviderInterface` and return an array of `Welp\MailChimpBundle\Subscriber\Subscriber` objects. The first argument of the `Subscriber` object is its e-mail, the second argument is an array of merge fields values you need to add in MailChimp's backend in your list settings under `List fields and *|MERGE|* tags` (see this [guide on MailChimp](http://kb.mailchimp.com/merge-tags/using/getting-started-with-merge-tags) to add merge tags in your list) and the third is an array of options [See MailChimp Documentation](http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/).

```php
<?php

namespace YourApp\App\Newsletter;

use Welp\MailchimpBundle\Provider\ProviderInterface;
use Welp\MailchimpBundle\Subscriber\Subscriber;
use YourApp\Model\User\UserRepository;
use YourApp\Model\User\User;

class ExampleSubscriberProvider implements ProviderInterface
{
    // these tags should match the one you added in MailChimp's backend
    const TAG_NICKNAME =           'NICKNAME';
    const TAG_GENDER =             'GENDER';
    const TAG_BIRTHDATE =          'BIRTHDATE';
    const TAG_LAST_ACTIVITY_DATE = 'LASTACTIVI';
    const TAG_REGISTRATION_DATE =  'REGISTRATI';
    const TAG_CITY =               'CITY';

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getSubscribers()
    {
        $users = $this->userRepository->findSubscribers();

        $subscribers = array_map(function(User $user) {
            $subscriber = new Subscriber($user->getEmail(), [
                self::TAG_NICKNAME => $user->getNickname(),
                self::TAG_GENDER => $user->getGender(),
                self::TAG_BIRTHDATE => $user->getBirthdate() ? $user->getBirthdate()->format('Y-m-d') : null,
                self::TAG_CITY => $user->getCity(),
                self::TAG_LAST_ACTIVITY_DATE => $user->getLastActivityDate() ? $user->getLastActivityDate()->format('Y-m-d') : null,
                self::TAG_REGISTRATION_DATE => $user->getRegistrationDate() ? $user->getRegistrationDate()->format('Y-m-d') : null,
            ],[
                'language'   => 'fr',
                'email_type' => 'html'
            ]);

            return $subscriber;
        }, $users);

        return $subscribers;
    }
}

```

We also provide a ready to use provider for the FosUserBundle -> FosSubscriberProvider. You just need to register the service into your app:

```yaml
services:
    yourapp_mailchimp_fos_subscriber_provider:
        class: Welp\MailchimpBundle\Provider\FosSubscriberProvider
        arguments: [@fos_user.user_manager]
```

After this, don't forget to add the service key for your list into your `config.yml`:

```yaml
    ...
    listId2:
        subscriber_provider: 'yourapp.provider2'
        ...
```

## Dynamic Subscriber Provider

If you want to create a reusable provider for multiple list without defining a service for each one, you can make use of the `Welp\MailChimpBundle\Provider\DynamicProviderInterface`. It works exactly the same as the `ProviderInterface` except it has one setter method that is used to set the current list in the synchronisation process.

Example implementation:

```php
<?php

namespace YourApp\App\Newsletter;

use Welp\MailchimpBundle\Provider\DynamicProviderInterface;
use Welp\MailchimpBundle\Subscriber\Subscriber;
use YourApp\Model\User\UserRepository;
use YourApp\Model\User\User;

class DynamicProvider implements DynamicProviderInterface
{
    
    // these tags should match the one you added in MailChimp's backend
    const TAG_NICKNAME =           'NICKNAME';
    const TAG_GENDER =             'GENDER';
    const TAG_BIRTHDATE =          'BIRTHDATE';
    const TAG_LAST_ACTIVITY_DATE = 'LASTACTIVI';
    const TAG_REGISTRATION_DATE =  'REGISTRATI';
    const TAG_CITY =               'CITY';

    protected $userRepository;
    protected $listId;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    // List id is set for each list in the synchronisation process
    public function setListId(string $listId)
    {
        $this->listId = $listId;
    }

    public function getSubscribers()
    {
        //now make use of the list id when querying for users
        $users = $this->userRepository->findSubscribersByListId($this->listId);

        $subscribers = array_map(function(User $user) {
            $subscriber = new Subscriber($user->getEmail(), [
                self::TAG_NICKNAME => $user->getNickname(),
                self::TAG_GENDER => $user->getGender(),
                self::TAG_BIRTHDATE => $user->getBirthdate() ? $user->getBirthdate()->format('Y-m-d') : null,
                self::TAG_CITY => $user->getCity(),
                self::TAG_LAST_ACTIVITY_DATE => $user->getLastActivityDate() ? $user->getLastActivityDate()->format('Y-m-d') : null,
                self::TAG_REGISTRATION_DATE => $user->getRegistrationDate() ? $user->getRegistrationDate()->format('Y-m-d') : null,
            ],[
                'language'   => 'fr',
                'email_type' => 'html'
            ]);

            return $subscriber;
        }, $users);

        return $subscribers;
    }
}


``` 