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
            $this->getListId($event->getListId()),
            $event->getSubscriber()
        );
    }

    public function onUnsubscribe(SubscriberEvent $event)
    {
        $this->listRepository->unsubscribe(
            $this->getListId($event->getListId()),
            $event->getSubscriber()
        );
    }

    protected function getListId($listId)
    {
        $listData = $this->listRepository->findById($listId);

        return $listData['id'];
    }
}
