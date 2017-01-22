<?php

namespace spec\Welp\MailchimpBundle\Event;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Welp\MailchimpBundle\Subscriber\Subscriber;
use Welp\MailchimpBundle\Subscriber\ListRepository;
use Welp\MailchimpBundle\Event\SubscriberEvent;

class SubscriberListenerSpec extends ObjectBehavior
{
    function let(ListRepository $listRepository, SubscriberEvent $event, Subscriber $subscriber)
    {
        $listRepository->findById('foo')->willReturn(['id' => 123]);

        $event->getListId()->willReturn('foo');
        $event->getSubscriber()->willReturn($subscriber);

        $this->beConstructedWith($listRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Event\SubscriberListener');
    }

    function it_listen_to_subscribe_events($listRepository, $event, $subscriber)
    {
        $listRepository->subscribe('foo', $subscriber)->shouldBeCalled();
        $this->onSubscribe($event);
    }

    function it_listen_to_unsubscribe_events($listRepository, $event, $subscriber)
    {
        $listRepository->unsubscribe('foo', $subscriber)->shouldBeCalled();
        $this->onUnsubscribe($event);
    }

    function it_listen_to_pending_events($listRepository, $event, $subscriber)
    {
        $listRepository->pending('foo', $subscriber)->shouldBeCalled();
        $this->onPending($event);
    }

    function it_listen_to_clean_events($listRepository, $event, $subscriber)
    {
        $listRepository->clean('foo', $subscriber)->shouldBeCalled();
        $this->onClean($event);
    }

    function it_listen_to_delete_events($listRepository, $event, $subscriber)
    {
        $listRepository->delete('foo', $subscriber)->shouldBeCalled();
        $this->onDelete($event);
    }
}
