# Setup

## Installation

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

[More configuration](configuration.md)
