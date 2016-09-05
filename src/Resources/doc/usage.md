# Usage

## Synchronize merge fields

Merge fields are values you can add to your subscribers (for example the firstname or birthdate of your user). You can then use these tags in your newsletters or create segments out of them.

To learn more about merge tags, please see this [guide on MailChimp](http://kb.mailchimp.com/merge-tags/using/getting-started-with-merge-tags).

To synchronize you need to create your **lists** in MailChimp backend first. Then you need to add them in your `config.yml` as shown in the [above configuration](configuration.md). The `options` you can provide are the same as the one found in [MailChimp API](http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/).

You can then synchronize the merge fields using the `php app/console welp:mailchimp:synchronize-merge-fields` command. Note that every tag that are present in MailChimp but are not defined in your configuration **will be deleted along with associated values**.

NOTE: MailChimp provide two default merge fields:

* FNAME: Firstname
* LNAME: Lastname

## Full synchronization with command

You can synchronize all subscribers of your project at once by calling the Symfony command `php app/console welp:mailchimp:synchronize-subscribers`.

It will first fetch all the subscribers already present in MailChimp and unsubscribe any subscribers that are not in your projet (they might have been deleted on the project side), it will then send all your subscribers to MailChimp, new subscribers will be added and existing subscribers will be updated.

NOTE: you must have configure and create your own [subscriber provider](subscriber-provider.md).

## Unit synchronization with events

If you want realtime synchronization, you can dispatch custom events on your controllers (or anywhere). The subscribe event can be used both for adding a new subscriber or updating an existing one.

### Subscribe new User

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
        new SubscriberEvent('your_list_id', $subscriber)
    );
}
```

### Unsubscribe a User

Unsubscribe is simpler, you only need the email, all merge fields will be ignored:

```php
<?php

use Welp\MailchimpBundle\Event\SubscriberEvent;
use Welp\MailchimpBundle\Subscriber\Subscriber;

// ...

public function unsubscribeUser(User $user)
{
    // ...

    $subscriber = new Subscriber($user->getEmail());

    $this->container->get('event_dispatcher')->dispatch(
        SubscriberEvent::EVENT_UNSUBSCRIBE,
        new SubscriberEvent('your_list_id', $subscriber)
    );
}
```

### Delete a User

And finally delete a User

Unsubscribe is simpler, you only need the email, all merge fields will be ignored:

```php
<?php

use Welp\MailchimpBundle\Event\SubscriberEvent;
use Welp\MailchimpBundle\Subscriber\Subscriber;

// ...

public function deleteUser(User $user)
{
    // ...

    $subscriber = new Subscriber($user->getEmail());

    $this->container->get('event_dispatcher')->dispatch(
        SubscriberEvent::EVENT_DELETE,
        new SubscriberEvent('your_list_id', $subscriber)
    );
}
```

## Retrieve MailChimp Object to make custom MailChimp API V3 requests

You can also retrieve the MailChimp Object which comes from the library [drewm/mailchimp-api](https://github.com/drewm/mailchimp-api).

The service key is `welp_mailchimp.mailchimp_master`.

Example:

``` php
<?php
// in any controller action...
    ...
    $MailChimp = $this->container->get('welp_mailchimp.mailchimp_master');
    $list_id = 'b1234346';

    $result = $MailChimp->post("lists/$list_id/members", [
                    'email_address' => 'davy@example.com',
                    'status'        => 'subscribed',
                ]);

    if ($MailChimp->success()) {
        print_r($result);   
    } else {
        echo $MailChimp->getLastError();
    }

```
