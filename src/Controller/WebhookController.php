<?php

namespace Welp\MailchimpBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Welp\MailchimpBundle\Event\WebhookEvent;
use Welp\MailchimpBundle\Provider\ListProviderInterface;

/**
 * @Route("/webhook")
 */
class WebhookController extends AbstractController
{
    private ListProviderInterface $listProvider;
    private EventDispatcherInterface $dispatcher;

    public function __construct(ListProviderInterface $listProvider, EventDispatcherInterface $dispatcher)
    {
        $this->listProvider = $listProvider;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Endpoint for the mailchimp list Webhook
     * https://apidocs.mailchimp.com/webhooks/
     *
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
        /** @var string $type */
        $type = $request->request->get('type');
        /** @var array $data */
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
        $list = $this->listProvider->getList($listId);

        // Check the webhook_secret
        $authorized = false;

        if ($list != null && $list->getWebhookSecret() == $hooksecret) {
            $authorized = true;
        }

        if (!$authorized) {
            throw new AccessDeniedHttpException('Webhook secret mismatch!');
        }

        // Trigger the right event
        switch ($type) {
            case 'subscribe':
                $this->dispatcher->dispatch(new WebhookEvent($data), WebhookEvent::EVENT_SUBSCRIBE);
                break;
            case 'unsubscribe':
                $this->dispatcher->dispatch(new WebhookEvent($data), WebhookEvent::EVENT_UNSUBSCRIBE);
                break;
            case 'profile':
                $this->dispatcher->dispatch(new WebhookEvent($data), WebhookEvent::EVENT_PROFILE);
                break;
            case 'cleaned':
                $this->dispatcher->dispatch(new WebhookEvent($data), WebhookEvent::EVENT_CLEANED);
                break;
            case 'upemail':
                $this->dispatcher->dispatch(new WebhookEvent($data), WebhookEvent::EVENT_UPEMAIL);
                break;
            case 'campaign':
                $this->dispatcher->dispatch(new WebhookEvent($data), WebhookEvent::EVENT_CAMPAIGN);
                break;
            default:
                throw new AccessDeniedHttpException('type mismatch!');
        }

        return new JsonResponse([
            'type' => $type,
            'data' => $data,
        ]);
    }
}
