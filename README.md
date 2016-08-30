# mailchimp-bundle

[![Build Status](https://travis-ci.org/welpdev/mailchimp-bundle.svg?branch=master)](https://travis-ci.org/welpdev/mailchimp-bundle)

This bundle will help you synchronise your project's newsletter subscribers into MailChimp.

You can [synchronize all your subscribers at once with a Symfony command](#full-synchronization-with-command) : new users will be added to MailChimp, existing users will be updated and user no longer in your project will be deleted from MailChimp.

You can also [synchronize subscribe / unsubscribe one at a time with events](#unit-synchronization-with-events).

Optionnaly, it also help you to [synchronize your list merge tags](#synchronize-merge-tags).

* [Setup](#setup)
* [Configuration](#configuration)
* [Usage](#usage)
    * [Synchronize merge tags](#synchronize-merge-tags)
    * [Full synchronization with command](#full-synchronization-with-command)
    * [Unit synchronization with events](#unit-synchronization-with-events)

## Setup

Add bundle to your project:

```bash
composer require welp/mailchimp-bundle
```

Add `Welp\MailChimpBundle\WelpMailChimpBundle` to your `AppKernel.php`:

```php
$bundles = [
    // ...
    new Welp\MailChimpBundle\WelpMailChimpBundle(),
];
```

## Configuration

You need to add the list in MailChimp's backend first.

For each list you must define a configuration in your `config.yml`:

```yaml
welp_mailchimp:
    api_key: YOURMAILCHIMPAPIKEY
    lists:
        list1:
            # optional language option, used only in full synchronization
            mc_language: 'fr'

            # optional merge tags you want to synchronize
            merge_tags:
                -
                    tag: FIRSTTAG
                    name: My first tag
                    options: {"field_type":"radio", "choices": ["foo", "bar"]}
                -
                    tag: SECONDTAG
                    name: My second tag

            # provider used in full synchronization
            subscriber_providers: 'yourapp.provider1'

        list2:
            subscriber_providers: 'yourapp.provider2'
```

Where `listX` is the name of your MailChimp lists, and `yourapp.providerX` is the key of your provider's service that will provide the subscribers that need to be synchronized in MailChimp. The key `mc_language` is optional and will set this language for all subscribers in this list, see [the list of accepted language codes](http://kb.mailchimp.com/lists/managing-subscribers/view-and-edit-subscriber-languages#code).

Defining lists and providers is necessary only if you use full synchronization with the command.

## Usage

### Synchronize merge tags

Merge tags (or merge vars) are values you can add to your subscribers (for example the firstsname or birthdate of your user). You can then use these tags in your newsletters or create segments out of them.

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

class SubscriberProvider implements ProviderInterface
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
                // you don't need to specify "mc_language" tag if you added it in your config
                // you can also use all MailChimp configuration tags here as well
            ]);

            return $subscriber;
        }, $users);

        return $subscribers;
    }
}
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
		'mc_language' => 'fr',
		// Important note : mc_language defined in config.yml will not be used, be sure to set it here if needed
		// as well as any other MailChimp tag you need.
	]);

	$this->container->get('event_dispatcher')->dispatch(
        SubscriberEvent::EVENT_SUBSCRIBE,
        new SubscriberEvent('your_list_name', $subscriber)
    );
}
```

If you want to tell MailChimp that an existing subscriber has changed its e-mail, you can do it by adding the `new-email` option to the merge tags:

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

Unsubscribe is simpler, you only need the email, all merge tags will be ignored:

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
