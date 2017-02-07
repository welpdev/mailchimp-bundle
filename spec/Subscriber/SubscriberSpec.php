<?php

namespace spec\Welp\MailchimpBundle\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SubscriberSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('charles@terrasse.fr');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Subscriber\Subscriber');
    }

    public function it_has_an_email()
    {
        $this->getEmail()->shouldReturn('charles@terrasse.fr');
    }

    public function it_has_default_merge_fields()
    {
        $this->getMergeFields()->shouldReturn([]);
    }

    public function it_can_have_merge_fields()
    {
        $this->beConstructedWith('charles@terrasse.fr', $tags = ['FIRSTNAME' => 'Charles']);
        $this->getMergeFields()->shouldReturn($tags);
    }

    public function it_can_have_merge_fields_with_null_value()
    {
        $this->beConstructedWith('charles@terrasse.fr', $tags = ['FIRSTNAME' => 'Charles', 'BIRTHDATE' => null]);
        $this->getMergeFields()->shouldReturn(['FIRSTNAME' => 'Charles']);
    }

    public function it_can_have_options()
    {
        $this->beConstructedWith('charles@terrasse.fr', $tags = ['FIRSTNAME' => 'Charles'], $options = ['language' => "fr"]);
        $this->getOptions()->shouldReturn($options);
    }

    public function it_format_for_mailchimp()
    {
        $this->beConstructedWith('charles@terrasse.fr', $tags = ['FIRSTNAME' => 'Charles'], $options = ['language' => "fr"]);
        $this->formatMailChimp()->shouldReturn([
            'email_address' => 'charles@terrasse.fr',
            'merge_fields' => $tags,
            'language' => "fr"
        ]);
    }

    public function it_format_for_mailchimp_without_mergetags()
    {
        $this->beConstructedWith('charles@terrasse.fr', [], $options = ['language' => "fr"]);
        $this->formatMailChimp()->shouldReturn([
            'email_address' => 'charles@terrasse.fr',
            'language' => "fr"
        ]);
    }

    public function it_format_for_mailchimp_without_options()
    {
        $this->beConstructedWith('charles@terrasse.fr', [], []);
        $this->formatMailChimp()->shouldReturn([
            'email_address' => 'charles@terrasse.fr'
        ]);
    }
}
