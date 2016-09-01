## Usage

### Synchronize merge fields

Merge fields are values you can add to your subscribers (for example the firstname or birthdate of your user). You can then use these tags in your newsletters or create segments out of them.

To learn more about merge tags, please see this [guide on MailChimp](http://kb.mailchimp.com/merge-tags/using/getting-started-with-merge-tags).

To synchronize you need to create your lists in MailChimp backend first. Then you need to add them in your `config.yml` as shown in the [above configuration](#configuration). The `options` you can provide are the same as the one found in [MailChimp API](https://apidocs.mailchimp.com/api/2.0/lists/merge-var-add.php).

You can then synchronize the tags using the `app/console welp:mailchimp:synchronize-merge-tags` command. Note that every tag that are present in MailChimp but are not defined in your configuration **will be deleted along with associated values**.

### Full synchronization with command

You can synchronize all subscribers of your project at once by calling the Symfony command `app/console welp:mailchimp:synchronize-subscribers`. It will first fetch all the subscribers already present in MailChimp and unsubscribe any subscribers that are not in your projet (they might have been deleted on the project side), it will then send all your subscribers to MailChimp, new subscribers will be added and existing subscribers will be updated.

After [configuring your lists](#configuration) in `config.yml`, you need to create at least one `Provider`that will be used by the Symfony command. Your provider should be accessible via a service key (the same you reference in `subscriber_providers` in the configuration above):

```yaml
services:
    yourapp_mailchimp_subscriber_provider:
        class: YourApp\App\Newsletter\SubscriberProvider
        arguments: [@yourapp_user_repository]
```

It should implement `Welp\MailChimpBundle\Provider\ProviderInterface` and return an array of `Welp\MailChimpBundle\Subscriber\Subscriber` objects. The first argument of the `Subscriber` object is its e-mail, the second argument is an array of merge tags values you need to add in MailChimp's backend in your list settings under `List fields and *|MERGE|* tags` (see this [guide on MailChimp](http://kb.mailchimp.com/merge-tags/using/getting-started-with-merge-tags) to add merge tags in your list).

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
            ]);

            return $subscriber;
        }, $users);

        return $subscribers;
    }
}

```

FosSubscriberProvider:

```yaml
services:
    yourapp_mailchimp_fos_subscriber_provider:
        class: Welp\MailchimpBundle\Provider\FosSubscriberProvider
        arguments: [@fos_user.user_manager]
```

### Unit synchronization with events

If you want realtime synchronization, you can dispatch custom events on your controllers (or anywhere). The subscribe event can be used both for adding a new subscriber or updating an existing one.

Here is an example of a subscribe event dispatch:

```php
<?php

use Welp\MailchimpBundle\Event\SubscriberEvent;
use Welp\MailchimpBundle\Subscriber\Subscriber;

// ...

public function newUser(User $user)
{
    // ...

    $subscriber = new Subscriber($user->getEmail(), [
		'FIRSTNAME' => $user->getFirstname(),
	], [
        'language' => 'fr'
    ]);

	$this->container->get('event_dispatcher')->dispatch(
        SubscriberEvent::EVENT_SUBSCRIBE,
        new SubscriberEvent('your_list_name', $subscriber)
    );
}
```

If you want to tell MailChimp that an existing subscriber has changed its e-mail, you can do it by adding the `new-email` option to the merge fields:

```php
<?php

use Welp\MailchimpBundle\Event\SubscriberEvent;
use Welp\MailchimpBundle\Subscriber\Subscriber;

// ...

public function changedEmail($previousMail, $newEmail)
{
    // ...

    $subscriber = new Subscriber($previousEmail, [
		 'new-email' => $newEmail
    ]);

    $this->container->get('event_dispatcher')->dispatch(
        SubscriberEvent::EVENT_SUBSCRIBE,
        new SubscriberEvent('your_list_name', $subscriber)
    );
}
```

Unsubscribe is simpler, you only need the email, all merge fields will be ignored:

```php
<?php

use Welp\MailchimpBundle\Event\SubscriberEvent;
use Welp\MailchimpBundle\Subscriber\Subscriber;

// ...

public function deletedUser(User $user)
{
    // ...

    $subscriber = new Subscriber($user->getEmail());

    $this->container->get('event_dispatcher')->dispatch(
        SubscriberEvent::EVENT_UNSUBSCRIBE,
        new SubscriberEvent('your_list_name', $subscriber)
    );
}
```
