<?php

namespace Welp\MailchimpBundle\Event;

use Welp\MailchimpBundle\Subscriber\ListRepository;
use Welp\MailchimpBundle\Event\SubscriberEvent;

class SubscriberListener
{
    protected $listRepository;

    public function __construct(ListRepository $listRepository)
    {
        $this->listRepository = $listRepository;
    }

    public function onSubscribe(SubscriberEvent $event)
    {
        $this->listRepository->subscribe(
            $event->getListId(),
            $event->getSubscriber()
        );
    }

    public function onUnsubscribe(SubscriberEvent $event)
    {
        $this->listRepository->unsubscribe(
            $event->getListId(),
            $event->getSubscriber()
        );
    }

    public function onUpdate(SubscriberEvent $event)
    {
        $this->listRepository->update(
            $event->getListId(),
            $event->getSubscriber()
        );
    }

    public function onChangeEmail(SubscriberEvent $event)
    {
        $this->listRepository->changeEmailAddress(
            $event->getListId(),
            $event->getOldEmail(),
            $event->getSubscriber()->getEmail()
        );
    }

    public function onDelete(SubscriberEvent $event)
    {
        $this->listRepository->delete(
            $event->getListId(),
            $event->getSubscriber()
        );
    }
}
