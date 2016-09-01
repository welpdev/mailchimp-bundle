<?php

namespace Welp\MailchimpBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use \DrewM\MailChimp\Webhook;

/**
 * @Route("/webhooks")
 */
class WebhookController extends Controller
{

    /**
     * Endpoint for the mailchimp list Webhook
     * https://apidocs.mailchimp.com/webhooks/
     * @Route("/", name="webhook_index")
     */
    public function indexAction(Request $request)
    {
        // @TODO secure webhook with a secret
        // retrieve data, format it, trig an event foreach type with data in params
        /*
            https://apidocs.mailchimp.com/webhooks/
            data[merges][FNAME]: Tztz
            data[merges][EMAIL]: tztz@gmail.com
            data[email_type]: html
            data[reason]: manual
            data[email]: tztz@gmail.com
            fired_at: 2016-09-01 10:55:13
            data[id]: 5c1b5a7c1e
            data[merges][LNAME]: TZST
            data[list_id]: ba039c6198
            data[web_id]: 3375995
            type: unsubscribe

            data[merges][FNAME]: Toto
            data[merges][EMAIL]: toto@gmail.com
            data[email_type]: html
            data[ip_opt]: 86.195.200.227
            data[email]: toto@gmail.com
            data[web_id]: 3017279
            data[list_id]: ba039c6198
            data[merges][LNAME]: TEST
            fired_at: 2016-09-01 10:55:11
            data[id]: e6311dfef4
            type: subscribe

            ...
        */

        return $request;
    }

}
