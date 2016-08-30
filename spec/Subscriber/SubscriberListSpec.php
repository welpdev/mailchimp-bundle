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

    function it_has_default_options()
    {
        $this->getOptions()->shouldReturn(['mc_language' => null]);
    }

    function it_can_set_options($provider)
    {
        $this->beConstructedWith('foobar', $provider, ['mc_language' => 'fr']);
        $this->getOptions()->shouldReturn(['mc_language' => 'fr']);
    }

    function it_cannot_set_any_option($provider)
    {
        $this
            ->shouldThrow(new \Exception('The option "bar" does not exist. Defined options are: "mc_language".'))
            ->during('__construct', ['foobar', $provider, ['bar' => 'foo']])
        ;
    }
}
