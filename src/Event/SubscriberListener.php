<?php

namespace Welp\MailchimpBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Welp\MailchimpBundle\Subscriber\ListRepository;

/**
 * Listener for subscriber unit synchronization
 */
class SubscriberListener extends Event
{
    /**
     * @var ListRepository
     */
    protected ListRepository $listRepository;

    /**
     * @param ListRepository $listRepository
     */
    public function __construct(ListRepository $listRepository)
    {
        $this->listRepository = $listRepository;
    }

    /**
     * Action when a User subscribe
     * @param  SubscriberEvent $event
     * @return void
     */
    public function onSubscribe(SubscriberEvent $event): void
    {
        $this->listRepository->subscribe(
            $event->getListId(),
            $event->getSubscriber()
        );
    }

    /**
     * Action when a User unsubscribe
     * @param  SubscriberEvent $event
     * @return void
     */
    public function onUnsubscribe(SubscriberEvent $event): void
    {
        $this->listRepository->unsubscribe(
            $event->getListId(),
            $event->getSubscriber()
        );
    }

    /**
     * Action when a User is pending
     * @param  SubscriberEvent $event
     * @return void
     */
    public function onPending(SubscriberEvent $event): void
    {
        $this->listRepository->pending(
            $event->getListId(),
            $event->getSubscriber()
        );
    }

    /**
     * Action when a User is cleaned
     * @param  SubscriberEvent $event
     * @return void
     */
    public function onClean(SubscriberEvent $event): void
    {
        $this->listRepository->clean(
            $event->getListId(),
            $event->getSubscriber()
        );
    }

    /**
     * Action when a User update its info
     * @param  SubscriberEvent $event
     * @return void
     */
    public function onUpdate(SubscriberEvent $event): void
    {
        $this->listRepository->update(
            $event->getListId(),
            $event->getSubscriber()
        );
    }

    /**
     * Action when a User change its email address
     * @param  SubscriberEvent $event
     * @return void
     */
    public function onChangeEmail(SubscriberEvent $event): void
    {
        $this->listRepository->changeEmailAddress(
            $event->getListId(),
            $event->getSubscriber(),
            $event->getOldEmail()
        );
    }

    /**
     * Action when a User is deleted
     * @param  SubscriberEvent $event
     * @return void
     */
    public function onDelete(SubscriberEvent $event): void
    {
        $this->listRepository->delete(
            $event->getListId(),
            $event->getSubscriber()
        );
    }
}
