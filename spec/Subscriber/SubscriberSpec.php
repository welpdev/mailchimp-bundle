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

    function it_has_default_merge_fields()
    {
        $this->getMergeFields()->shouldReturn([]);
    }

    function it_can_have_merge_fields()
    {
        $this->beConstructedWith('charles@terrasse.fr', $tags = ['FIRSTNAME' => 'Charles']);
        $this->getMergeFields()->shouldReturn($tags);
    }

    function it_can_have_merge_fields_options()
    {
        $this->beConstructedWith('charles@terrasse.fr', $tags = ['FIRSTNAME' => 'Charles'], $options = ['language' => "fr"]);
        $this->getMergeFields()->shouldReturn($tags);
        $this->getOptions()->shouldReturn($options);
    }

    function it_format_for_mailchimp()
    {
        $this->beConstructedWith('charles@terrasse.fr', $tags = ['FIRSTNAME' => 'Charles'], $options = ['language' => "fr"]);
        $this->formatMailChimp()->shouldReturn([
            'email_address' => 'charles@terrasse.fr',
            'merge_fields' => $tags,
            'language' => "fr"
        ]);
    }
}
