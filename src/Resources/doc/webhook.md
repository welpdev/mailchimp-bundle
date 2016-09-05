# Webhook

* https://apidocs.mailchimp.com/webhooks/

## Configuration

app/routing.yml

    myapp_mailchimp_webhook:
        resource: "@WelpMailchimpBundle/Resources/config/routing.yml"
        prefix:   /mailchimp

http://domain.com/mailchimp/webhook/endpoint

config.yml

    welp_mailchimp:
        api_key: 3419ca97412af7c2893b89894275b415-us14
        lists:
            ba039c6198:
                webhook_secret: thisisTheSecretPass
                ...


## Command to register webhook to lists



## Events to listen

Create listener
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

Register the listener into services.yml

    services:
        app.listener.mailchimp.webhook:
            class: AppBundle\Listener\MailchimpEventListener
            tags:
                - { name: kernel.event_subscriber }
            arguments:
                - @service_container

Test with ngrok and you will see in app log:

    ...
    [2016-09-05 11:55:48] app.INFO: Unsubscribe Event: {"reason":"manual","id":"5c1b5a7c1e","email":"tztz@gmail.com","email_type":"html","web_id":"3375995","merges":{"EMAIL":"tztz@gmail.com","FNAME":"Tztz","LNAME":"TZST"},"list_id":"ba039c6198"} []
