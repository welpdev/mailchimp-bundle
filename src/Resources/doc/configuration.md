# Configuration

You need to add the list in MailChimp's backend first.

## config.yml

For each list you want to sync you must define a configuration in your `config.yml`:

```yaml
welp_mailchimp:
    api_key: YOURMAILCHIMPAPIKEY
    list_provider: 'welp_mailchimp.list_provider'
    lists:
        listId1:
            # provider used in full synchronization
            subscriber_provider: 'yourapp.provider1'
            # webhook secret to secure webhook between MailChimp & your app
            webhook_secret: 'thisIsASecret'
            # The webhook url to be registered to the MailChimp list
            webhook_url: 'https://myapp.com/mailchimp/webhook/endpoint'
            # optional merge tags you want to synchronize
            merge_fields:
                -
                    tag: FNAME
                    name: First Name
                    type: text
                    public: true
                -
                    tag: LNAME
                    name: Last Name
                    type: text
                    public: true
                -
                    tag: FIRSTTAG
                    name: My first tag
                    type: text
                    options:
                        size: 5
                        ...
                -
                    tag: SECONDTAG
                    name: My second tag
                    type: text
                    public: true
                    ...

        listId2:
            subscriber_provider: 'yourapp.provider2'
            ...
```

Where `listIdX` is the list id of your MailChimp lists, and `yourapp.providerX` is the key of your provider's service that will provide the subscribers that need to be synchronized in MailChimp. See the documentation on create [your own Subscriber provider](subscriber-provider.md).

Defining lists and providers is only necessary if you use full synchronization with the command.

Defining webhook_* is only necessary if you want to use webhook automation.

## Merge fields configuration

* [MailChimp documentation](http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/)

You can find all parameters in the MailChimp documentation.

Example:

```yaml
    merge_fields:
        -
            tag: FIRSTTAG
            name: My first tag
            type: text
            options:
                size: 5
                ...
        -
            tag: SECONDTAG
            name: My second tag
            type: text
            public: true
            ...
        -
            tag: FULLTEST
            name: Full Test
            type: text
            required: true
            default_value: test
            public: false
            display_order: 1
            options:
                default_country: 1546
                phone_format: International
                date_format: dd/mm/yyyy
                choices:
                    ...
                size: 50
            help_text: this is a full test

```

Note: MailChimp provides two default merge fields (FNAME & LNAME). You can use this config to handle them:

```yaml
    ...
    merge_fields:
        -
            tag: FNAME
            name: First Name
            type: text
            public: true
        -
            tag: LNAME
            name: Last Name
            type: text
            public: true
```
