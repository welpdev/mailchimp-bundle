<?php

namespace Welp\MailchimpBundle\Controller;

use Psr\Container\ContainerExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Welp\MailchimpBundle\Provider\ListProviderInterface;
use Welp\MailchimpBundle\Event\WebhookEvent;

#[Route('/webhook')]
class WebhookController extends AbstractController
{

    /**
     * Endpoint for the mailchimp list Webhook
     * https://apidocs.mailchimp.com/webhooks/
     * @param Request $request
     * @param EventDispatcherInterface $eventDispatcher
     * @return JsonResponse
     */
    #[Route('/endpoint', name: 'webhook_index')]
    public function indexAction(Request $request, EventDispatcherInterface $eventDispatcher): JsonResponse
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
        $data = $request->request->all('data');
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
            throw $this->createAccessDeniedException('Incorrect data format!');
        }

        $listId = $data['list_id'];

        $listProviderKey = $this->getParameter('welp_mailchimp.list_provider');

        try {
            $listProvider = $this->get($listProviderKey);
        } catch (ContainerExceptionInterface $e) {
            throw new \InvalidArgumentException(sprintf('List Provider "%s" should be defined as a service.', $listProviderKey), $e->getCode(), $e);
        }

        if (!$listProvider instanceof ListProviderInterface) {
            throw new \InvalidArgumentException(sprintf('List Provider "%s" should implement Welp\MailchimpBundle\Provider\ListProviderInterface.', $listProviderKey));
        }

        $list = $listProvider->getList($listId);

        // Check the webhook_secret
        $authorized = false;

        if ($list !== null && $list->getWebhookSecret() === $hooksecret) {
            $authorized = true;                           
        }

        if (!$authorized) {
            throw $this->createAccessDeniedException('Webhook secret mismatch!');
        }

        $eventName = match ($type) {
            'subscribe' => WebhookEvent::EVENT_SUBSCRIBE,
            'unsubscribe' => WebhookEvent::EVENT_UNSUBSCRIBE,
            'profile' => WebhookEvent::EVENT_PROFILE,
            'cleaned' => WebhookEvent::EVENT_CLEANED,
            'upemail' => WebhookEvent::EVENT_UPEMAIL,
            'campaign' => WebhookEvent::EVENT_CAMPAIGN,
            default => throw $this->createAccessDeniedException('Type mismatch!'),
        };

        $eventDispatcher->dispatch(new WebhookEvent($data), $eventName);

        return new JsonResponse([
            'type' => $type,
            'data' => $data,
        ]);
    }
}
