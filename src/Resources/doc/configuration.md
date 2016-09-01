# Configuration

You need to add the list in MailChimp's backend first.

For each list you must define a configuration in your `config.yml`:

```yaml
welp_mailchimp:
    api_key: YOURMAILCHIMPAPIKEY
    lists:
        listId1:
            # optional merge tags you want to synchronize
            merge_fields:
                -
                    tag: FIRSTTAG
                    name: My first tag
                    type: text
                    options:
                        size: 5
                -
                    tag: SECONDTAG
                    name: My second tag
                    type: text

            # provider used in full synchronization
            subscriber_providers: 'yourapp.provider1'

        listId2:
            subscriber_providers: 'yourapp.provider2'
```

Where `listIdX` is the listId of your MailChimp lists, and `yourapp.providerX` is the key of your provider's service that will provide the subscribers that need to be synchronized in MailChimp.

Defining lists and providers is necessary only if you use full synchronization with the command.
