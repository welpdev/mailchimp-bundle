# mailchimp-bundle

[![Build Status](https://travis-ci.org/welpdev/mailchimp-bundle.svg?branch=master)](https://travis-ci.org/welpdev/mailchimp-bundle)
[![Packagist](https://img.shields.io/packagist/v/welp/mailchimp-bundle.svg)](https://packagist.org/packages/welp/mailchimp-bundle)
[![Packagist](https://img.shields.io/packagist/dt/welp/mailchimp-bundle.svg)](https://packagist.org/packages/welp/mailchimp-bundle)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/welpdev/mailchimp-bundle/master/LICENSE.md)

This bundle will help you synchronise your project's newsletter subscribers into MailChimp throught MailChimp API V3.

## Features

* Use your own userProvider (basic `FosSubscriberProvider` included to interface with FosUserBundle)
* Synchronize Merge Fields with your config
* Synchronize your subscriber with a List
* Use lifecycle event to subscribe/unsubscribe/delete subscriber from a List
* Retrieve [MailChimp Object](https://github.com/drewm/mailchimp-api) to make custom MailChimp API V3 requests
* Register Webhooks

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

## Minimal Configuration

In your `config.yml`:

```yaml
welp_mailchimp:
    api_key: YOURMAILCHIMPAPIKEY
```

More configuration on the [documentation](src/Resources/doc/configuration.md).

## Documentation

* [Setup](src/Resources/doc/setup.md)
* [Configuration](src/Resources/doc/configuration.md)
* [Subscriber Provider](src/Resources/doc/subscriber-provider.md)
* [Usage](src/Resources/doc/usage.md)
    * Synchronize merge fields
    * Full synchronization with command
    * Unit synchronization with events
        * Subscribe new User
        * Unsubscribe a User
        * Delete a User
    * Retrieve [MailChimp Object](https://github.com/drewm/mailchimp-api) to make custom MailChimp API V3 requests
* [Webhook](src/Resources/doc/webhook.md)
    * Update User when subscribe/unsubscribe
