<?php

namespace Welp\MailchimpBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Welp\MailchimpBundle\Provider\ListProviderInterface;
use Welp\MailchimpBundle\Event\WebhookEvent;

/**
 * @Route("/webhook")
 */
class WebhookController extends Controller
{

    /**
     * Endpoint for the mailchimp list Webhook
     * https://apidocs.mailchimp.com/webhooks/
     * @Route("/endpoint", name="webhook_index")
     * @Method({"POST", "GET"})
     * @param Request $request
     * @throws AccessDeniedHttpException
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {

        // For Mailchimp ping GET
        if ($request->isMethod('GET')) {
            return new JsonResponse([
                'success' => true,
                'ping' => 'pong',
            ]);
        }

        // Handle POST request of Mailchimp
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

        if (empty($type) || empty($data) || empty($hooksecret) || !array_key_exists('list_id', $data)) {
            throw new AccessDeniedHttpException('incorrect data format!');
        }

        $listId = $data['list_id'];

        $listProviderKey = $this->getParameter('welp_mailchimp.list_provider');
        try {
            $listProvider = $this->get($listProviderKey); 
        } catch (ServiceNotFoundException $e) {
            throw new \InvalidArgumentException(sprintf('List Provider "%s" should be defined as a service.', $listProviderKey), $e->getCode(), $e);
        }

        if (!$listProvider instanceof ListProviderInterface) {
            throw new \InvalidArgumentException(sprintf('List Provider "%s" should implement Welp\MailchimpBundle\Provider\ListProviderInterface.', $listProviderKey));
        }

        $list = $listProvider->getList($listId);

        // Check the webhook_secret
        $authorized = false;
        if($list != null && $list->getWebhookSecret() == $hooksecret) {
            $authorized = true;                           
        }

        if (!$authorized) {
            throw new AccessDeniedHttpException('Webhook secret mismatch!');
        }

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
            'type' => $type,
            'data' => $data,
        ]);
    }
}
