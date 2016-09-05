# Webhook

* [Documentation](https://apidocs.mailchimp.com/webhooks/)
* [How to set up webhooks](http://kb.mailchimp.com/integrations/api-integrations/how-to-set-up-webhooks)
* [Webhooks API V3](http://developer.mailchimp.com/documentation/mailchimp/reference/lists/webhooks/)

Webhooks will be triggered when an event occured in MailChimp, and it will call our webhook url and fired Webhook Events in our Symfony App. We will listen to these events in order to add our logic/workflow.

## Configuration

You need to add the webhook routing to your app routing:

`app/routing.yml`

    myapp_mailchimp_webhook:
        resource: "@WelpMailchimpBundle/Resources/config/routing.yml"
        prefix:   /mailchimp

Note: you can change the prefix as you like.

This will generate an url to the webhook like this: <http://domain.com/mailchimp/webhook/endpoint>

Also, MailChimp recommand to protect webhook url with a token parameter. So you need to add the secret token to your list in your config.yml

`config.yml`

    welp_mailchimp:
        api_key: 3419ca97412af7c2893b89894275b415-us14
        lists:
            ba039c6198:
                webhook_secret: thisisTheSecretPass
                ...

Note: To access properly to the webhook function you will have to use the url with the secret parameter: <http://domain.com/mailchimp/webhook/endpoint?hooksecret=thisisTheSecretPass>

## Register the webhook manually

See [How to set up webhooks](http://kb.mailchimp.com/integrations/api-integrations/how-to-set-up-webhooks).

And the webhook url you have to register is: <http://domain.com/mailchimp/webhook/endpoint?hooksecret=thisisTheSecretPass>


## Command to automatically register webhook to lists

There is a command to automatically register webhook to lists

Before using it, you have to add the webhook_url into lists in `config.yml`

`config.yml`

    welp_mailchimp:
        api_key: 3419ca97412af7c2893b89894275b415-us14
        lists:
            ba039c6198:
                webhook_secret: thisisTheSecretPass
                webhook_url: http://domain.com/mailchimp/webhook/endpoint

Next in your terminal use this command `php app/console welp:mailchimp:webhook`. You can verify in your MailChimp List that the webhook has been added.

## Events to listen

In order to integrate MailChimp into your app workflow, you can listen to different Event.

Event you can listen:

    WebhookEvent::EVENT_SUBSCRIBE = 'welp.mailchimp.webhook.subscribe';
    WebhookEvent::EVENT_UNSUBSCRIBE = 'welp.mailchimp.webhook.unsubscribe';
    WebhookEvent::EVENT_PROFILE = 'welp.mailchimp.webhook.profile';
    WebhookEvent::EVENT_CLEANED = 'welp.mailchimp.webhook.cleaned';
    WebhookEvent::EVENT_UPEMAIL = 'welp.mailchimp.webhook.upemail';
    WebhookEvent::EVENT_CAMPAIGN = 'welp.mailchimp.webhook.campaign';

Example:

### 1- Create listener

``` php
    <?php

    namespace AppBundle\Listener;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    use Welp\MailchimpBundle\Event\WebhookEvent;



    class MailchimpEventListener implements EventSubscriberInterface
    {

        protected $container;


        public function __construct($container)
        {
            $this->container = $container;
        }

        public static function getSubscribedEvents()
        {
            return [
                WebhookEvent::EVENT_SUBSCRIBE => 'subscribe',
                WebhookEvent::EVENT_UNSUBSCRIBE => 'unsubscribe',
                WebhookEvent::EVENT_PROFILE => 'profile',
                WebhookEvent::EVENT_CLEANED => 'cleaned',
                WebhookEvent::EVENT_UPEMAIL => 'upemail',
                WebhookEvent::EVENT_CAMPAIGN => 'campaign'
            ];
        }

        public function subscribe(WebhookEvent $event){
            $logger = $this->container->get('logger');
            $logger->info('Subscribe Event:', $event->getData());
        }

        public function unsubscribe(WebhookEvent $event){
            $logger = $this->container->get('logger');
            $logger->info('Unsubscribe Event:', $event->getData());
        }

        public function profile(WebhookEvent $event){
            $logger = $this->container->get('logger');
            $logger->info('Profile Event:', $event->getData());
        }

        public function cleaned(WebhookEvent $event){
            $logger = $this->container->get('logger');
            $logger->info('Cleaned Event:', $event->getData());
        }

        public function upemail(WebhookEvent $event){
            $logger = $this->container->get('logger');
            $logger->info('Upemail Event:', $event->getData());
        }

        public function campaign(WebhookEvent $event){
            $logger = $this->container->get('logger');
            $logger->info('campaign Event:', $event->getData());
        }


    }
```

### 2- Register the listener into services.yml

    services:
        app.listener.mailchimp.webhook:
            class: AppBundle\Listener\MailchimpEventListener
            tags:
                - { name: kernel.event_subscriber }
            arguments:
                - @service_container

### 3- Test with ngrok (or other localhost tunnel) and you will see the result in app log:

    ...
    [2016-09-05 11:55:48] app.INFO: Unsubscribe Event: {"reason":"manual","id":"5c1b5a7c1e","email":"tztz@gmail.com","email_type":"html","web_id":"3375995","merges":{"EMAIL":"tztz@gmail.com","FNAME":"Tztz","LNAME":"TZST"},"list_id":"ba039c6198"} []
