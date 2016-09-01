<?php

namespace Welp\MailchimpBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use \DrewM\MailChimp\Webhook;
use Welp\MailchimpBundle\Event\WebhookEvent;

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

        $type = $request->request->get('type');
        $data = $request->request->get('data');
        /* Response example:
            data[merges][FNAME]: Tztz
            data[merges][EMAIL]: tztz@gmail.com
            data[email_type]: html
            data[reason]: manual
            data[email]: tztz@gmail.com
            data[id]: 5c1b5a7c1e
            data[merges][LNAME]: TZST
            data[list_id]: ba039c6198
            data[web_id]: 3375995
        */
        $hooksecret = $request->query->get('hooksecret');

        $listId = $data['list_id'];
        $lists = $this->getParameter('welp_mailchimp.lists');

        // Check the webhook_secret
        $authorized = false;
        foreach ($lists as $id => $listParameters) {
            if($listId ==  $id){
                if($listParameters['webhook_secret'] == $hooksecret){
                    $authorized = true;
                }
            }
        }

        if(!$authorized)
            throw new AccessDeniedHttpException('Webhook secret mismatch!');

        // Trigger the right event
        switch ($type) {
            case 'subscribe':
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(WebhookEvent::EVENT_SUBSCRIBE, new WebhookEvent($data));
                break;
            case 'unsubscribe':
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(WebhookEvent::EVENT_UNSUBSCRIBE, new WebhookEvent($data));
                break;
            case 'profile':
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(WebhookEvent::EVENT_PROFILE, new WebhookEvent($data));
                break;
            case 'cleaned':
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(WebhookEvent::EVENT_CLEANED, new WebhookEvent($data));
                break;
            case 'upemail':
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(WebhookEvent::EVENT_UPEMAIL, new WebhookEvent($data));
                break;
            case 'campaign':
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(WebhookEvent::EVENT_CAMPAIGN, new WebhookEvent($data));
                break;
            default:
                throw new AccessDeniedHttpException('type mismatch!');
                break;
        }

        return new JsonResponse([
            'type' => $type
            'data' => $data,
        ]);
    }

}
