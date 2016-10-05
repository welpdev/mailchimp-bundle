<?php

namespace spec\Welp\MailchimpBundle\Event;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Welp\MailchimpBundle\Subscriber\Subscriber;

class SubscriberEventSpec extends ObjectBehavior
{
    function let(Subscriber $subscriber)
    {
        $this->beConstructedWith('1337', $subscriber);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Event\SubscriberEvent');
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
    }

    function it_has_a_listname()
    {
        $this->getListId()->shouldReturn('1337');
    }

    function it_has_a_subscriber($subscriber)
    {
        $this->getSubscriber()->shouldReturn($subscriber);
    }

    function it_has_old_email($subscriber){
        $this->beConstructedWith('1337', $subscriber, 'oldemail@free.fr');
        $this->getOldEmail('oldemail@free.fr');

    }
}
