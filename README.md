# mailchimp-bundle

[![Build Status](https://travis-ci.org/welpdev/mailchimp-bundle.svg?branch=master)](https://travis-ci.org/welpdev/mailchimp-bundle)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/welpdev/mailchimp-bundle/master/LICENSE.md)

This bundle will help you synchronise your project's newsletter subscribers into MailChimp throught MailChimp API V3.

Features:

* Use your own userProvider (basic `FosSubscriberProvider` included to interface with FosUserBundle)
* Synchronize Merge Fields with your config
* Synchronize your subscriber with a List
* Use lifecycle event to subscribe/unsubscribe/delete subscriber from a List
* Retrieve [MailChimp Object](https://github.com/drewm/mailchimp-api) to make custom MailChimp API V3 requests
* Register Webhook (@TODO)

* [Setup](src/Resources/setup.md)
* [Configuration](src/Resources/configuration.md)
* [Usage](src/Resources/usage.md)
    * Synchronize merge fields
    * Full synchronization with command
    * Unit synchronization with events
        * Subscribe new User
        * Unsubscribe User
        * Delete User
    * Webhook
        * Update User when subscribe/unsubscribe

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

More configuration on the [documentation](src/Resources/configuration.md).
