<?php

namespace spec\Welp\MailchimpBundle\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SubscriberSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('charles@terrasse.fr');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Subscriber\Subscriber');
    }

    function it_has_an_email()
    {
        $this->getEmail()->shouldReturn('charles@terrasse.fr');
    }

    function it_has_default_merge_tags()
    {
        $this->getMergeTags()->shouldReturn([]);
    }

    function it_can_have_merge_tags()
    {
        $this->beConstructedWith('charles@terrasse.fr', $tags = ['FIRSTNAME' => 'Charles']);
        $this->getMergeTags()->shouldReturn($tags);
    }
}
