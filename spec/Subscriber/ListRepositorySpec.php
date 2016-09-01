<?php

namespace spec\Welp\MailchimpBundle\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use \DrewM\MailChimp\MailChimp;
use Welp\MailchimpBundle\Subscriber\Subscriber;

class ListRepositorySpec extends ObjectBehavior
{
    function let(MailChimp $mailchimp, Subscriber $subscriber)
    {
        $this->prepareSubscriber($subscriber);
        $this->prepareMailchimpLists($mailchimp);

        $this->beConstructedWith($mailchimp);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Subscriber\ListRepository');
    }

    function it_can_get_the_mailchimp_object()
    {
        $this->getMailChimp()->shouldHaveType('\DrewM\MailChimp\MailChimp');
    }

    function it_can_find_a_list_by_its_id(MailChimp $mailchimp)
    {
        $this->findById('ba039c6198')->shouldReturn(['id' => 'ba039c6198', 'name' => 'myList']);
    }

    function it_can_not_find_a_list_by_its_id(MailChimp $mailchimp)
    {
        $mailchimp->success()->willReturn(false);
        $mailchimp->getLastError()->willReturn('404: The requested resource could not be found.');

        $this->shouldThrow(new \Exception('404: The requested resource could not be found.'))
            ->duringFindById('notfound');
    }

    function it_subscribe_a_subscriber(MailChimp $mailchimp, $subscriber)
    {
        $this->subscribe('ba039c6198', $subscriber)->shouldReturn([
                'email_address' => 'charles@terrasse.fr',
                'merge_fields'  => ['FNAME' => 'Charles', 'LNAME' => 'Terrasse'],
                'language' => 'fr',
                'email_type'    => 'html',
                'status'        => 'subscribed'
            ]);
    }

    function it_unsubscribe_a_subscriber(MailChimp $mailchimp, $subscriber)
    {
        $mailchimp->patch("lists/ba039c6198/members/md5ofthesubscribermail", [
                'status'  => 'unsubscribed'
            ])->willReturn('unsubscribed');

        $this->unsubscribe('ba039c6198', $subscriber)->shouldReturn('unsubscribed');
    }

    function it_delete_a_subscriber(MailChimp $mailchimp, $subscriber)
    {
        $mailchimp->delete("lists/ba039c6198/members/md5ofthesubscribermail")->willReturn('deleted');

        $this->delete('ba039c6198', $subscriber)->shouldReturn('deleted');
    }
    /*

    function it_finds_merge_tags($mailchimpLists)
    {
        $mailchimpLists
            ->mergeVars([123])
            ->willReturn([
                'data' => [
                    [
                        'id' => 123,
                        'merge_vars' => [
                            ['tag' => 'EMAIL'], //this tag should be ignored
                            ['tag' => 'FOO', 'name' => 'Bar'],
                        ]
                    ]
                ]
            ])
        ;

        $this->findMergeTags(123)->shouldReturn([['tag' => 'FOO', 'name' => 'Bar']]);
    }

    function it_deletes_a_merge_tag($mailchimpLists, $logger)
    {
        $mailchimpLists->mergeVarDel(123, 'FOO')->shouldBeCalled();

        $this->deleteMergeTag(123, 'FOO');

        $logger->info('Tag "FOO" has been removed from MailChimp.');
    }

    function it_adds_a_merge_tag($mailchimpLists, $logger)
    {
        $mailchimpLists->mergeVarAdd(123, 'FOO', 'Foo bar', ['req' => true])->shouldBeCalled();

        $this->addMergeTag(123, [
            'tag' => 'FOO',
            'name' => 'Foo bar',
            'options' => ['req' => true]
        ]);

        $logger->info('Tag "FOO" has been added to MailChimp.');
    }

    function it_updates_a_merge_tag($mailchimpLists, $logger)
    {
        $mailchimpLists->mergeVarUpdate(123, 'FOO', ['name' => 'Foo bar', 'req' => true])->shouldBeCalled();

        $this->updateMergeTag(123, [
            'tag' => 'FOO',
            'name' => 'Foo bar',
            'options' => [
                'req' => true,
                'field_type' => 'text' // should be removed, cannot be updated
            ]
        ]);

        $logger->info('Tag "FOO" has been updated in MailChimp.');
    }*/

    protected function prepareSubscriber(Subscriber $subscriber)
    {
        $subscriber->getEmail()->willReturn('charles@terrasse.fr');
        $subscriber->getMergeFields()->willReturn(['FNAME' => 'Charles', 'LNAME' => 'Terrasse']);
        $subscriber->getOptions()->willReturn(['language' => 'fr', 'email_type' => 'html']);
        $subscriber->formatMailChimp()->willReturn(['email_address' => 'charles@terrasse.fr', 'merge_fields'  => ['FNAME' => 'Charles', 'LNAME' => 'Terrasse'], 'language' => 'fr', 'email_type' => 'html']);
    }

    protected function prepareMailchimpLists(MailChimp $mailchimp)
    {
        // subscriber hash
        $mailchimp->subscriberHash('charles@terrasse.fr')->willReturn('md5ofthesubscribermail');
        // success
        $mailchimp->success()->willReturn(true);
        // get the list
        $mailchimp->get("lists/ba039c6198")->willReturn(['id' => 'ba039c6198', 'name' => 'myList']);
        $mailchimp->get("lists/notfound")->willReturn(null);
        // subscribe member
        $mailchimp->post("lists/ba039c6198/members", [
                'email_address' => 'charles@terrasse.fr',
                'merge_fields'  => ['FNAME' => 'Charles', 'LNAME' => 'Terrasse'],
                'language' => 'fr',
                'email_type'    => 'html',
                'status'        => 'subscribed'
            ])->willReturn([
                'email_address' => 'charles@terrasse.fr',
                'merge_fields'  => ['FNAME' => 'Charles', 'LNAME' => 'Terrasse'],
                'language' => 'fr',
                'email_type'    => 'html',
                'status'        => 'subscribed'
            ]);
    }

    protected function getSubscriberChunk($count, $offset)
    {
        $subscribers = [];
        for ($i = $offset; $i < $offset + $count; $i++) {
            $subscribers[] = new Subscriber(sprintf('email%s@example.org', $i));
        }

        return $subscribers;
    }
}
