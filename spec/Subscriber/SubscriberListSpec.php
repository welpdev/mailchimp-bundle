<?php

namespace spec\Welp\MailchimpBundle\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Welp\MailchimpBundle\Provider\ProviderInterface;

class SubscriberListSpec extends ObjectBehavior
{
    function let(ProviderInterface $provider)
    {
        $this->beConstructedWith('1337', $provider);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Subscriber\SubscriberList');
    }

    function it_has_a_name()
    {
        $this->getListId()->shouldReturn('1337');
    }

    function it_has_a_provider($provider)
    {
        $this->getProvider()->shouldReturn($provider);
    }
}
