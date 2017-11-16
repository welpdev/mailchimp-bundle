# mailchimp-bundle

[![Build Status](https://travis-ci.org/welpdev/mailchimp-bundle.svg?branch=master)](https://travis-ci.org/welpdev/mailchimp-bundle)
[![Packagist](https://img.shields.io/packagist/v/welp/mailchimp-bundle.svg)](https://packagist.org/packages/welp/mailchimp-bundle)
[![Packagist](https://img.shields.io/packagist/dt/welp/mailchimp-bundle.svg)](https://packagist.org/packages/welp/mailchimp-bundle)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/welpdev/mailchimp-bundle/master/LICENSE.md)

This bundle will help you synchronise your project's newsletter subscribers into MailChimp through MailChimp API V3.

## Features

* [x] Use your own userProvider (basic `FosSubscriberProvider` included to interface with FosUserBundle)
* [x] Use your own listProvider (`DoctrineListProvider` included to retrieve your list from a database)
* [x] Synchronize Merge Fields with your config
* [x] Synchronize your subscriber with a List
* [x] Use lifecycle event to subscribe/unsubscribe/delete subscriber from a List
* [x] Retrieve [MailChimp Object](https://github.com/drewm/mailchimp-api) to make custom MailChimp API V3 requests
* [x] Register Webhooks

## Setup

Add bundle to your project:

```bash
composer require welp/mailchimp-bundle
```

Add `Welp\MailchimpBundle\WelpMailchimpBundle` to your `AppKernel.php`:

```php
$bundles = [
    // ...
    new Welp\MailchimpBundle\WelpMailchimpBundle(),
];
```

## Minimal Configuration

In your `config.yml`:

```yaml
welp_mailchimp:
    api_key: YOURMAILCHIMPAPIKEY
```

More configuration on the [documentation](configuration.md).

## Documentation

* [Setup](setup.md)
* [Configuration](configuration.md)
* [Subscriber Provider](subscriber-provider.md)
* [List Provider](list-provider.md)
* [Usage](usage.md)
    * Synchronize merge fields
    * Full synchronization with command
    * Unit synchronization with events
        * Subscribe new User
        * Unsubscribe a User
        * Update a User
        * Change User's email address (WORKAROUND)
        * Delete a User
    * Retrieve [MailChimp Object](https://github.com/drewm/mailchimp-api) to make custom MailChimp API V3 requests
* [Webhook](webhook.md)
    * Update User when subscribe/unsubscribe

## Contributing

If you want to contribute to this project, look at [over here](CONTRIBUTING.md)
