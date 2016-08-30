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
        $listRepository->findByName('foo')->willReturn(['id' => 123]);

        $event->getListName()->willReturn('foo');
        $event->getSubscriber()->willReturn($subscriber);

        $this->beConstructedWith($listRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Event\SubscriberListener');
    }

    function it_listen_to_subscribe_events($listRepository, $event, $subscriber)
    {
        $listRepository->subscribe(123, $subscriber)->shouldBeCalled();
        $this->onSubscribe($event);
    }

    function it_listen_to_unsubscribe_events($listRepository, $event, $subscriber)
    {
        $listRepository->unsubscribe(123, $subscriber)->shouldBeCalled();
        $this->onUnsubscribe($event);
    }
}
