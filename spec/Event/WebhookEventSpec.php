<?php

namespace spec\Welp\MailchimpBundle\Event;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WebhookEventSpec extends ObjectBehavior
{
    function let($data = ['test' => 0, 'data' => 154158])
    {
        $this->beConstructedWith($data);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Event\WebhookEvent');
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
    }

    function it_has_data($data)
    {
        $this->getData()->shouldReturn($data);
    }
}
