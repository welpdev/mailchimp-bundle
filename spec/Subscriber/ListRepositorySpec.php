<?php

namespace spec\Welp\MailchimpBundle\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use \DrewM\MailChimp\MailChimp;
use Welp\MailchimpBundle\Subscriber\Subscriber;
use Welp\MailchimpBundle\Exception\MailchimpException;

class ListRepositorySpec extends ObjectBehavior
{
    public function let(MailChimp $mailchimp, Subscriber $subscriber)
    {
        $this->prepareSubscriber($subscriber);
        $this->prepareMailchimpLists($mailchimp);      
          
        $this->beConstructedWith($mailchimp);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Subscriber\ListRepository');
    }

    public function it_can_get_the_mailchimp_object()
    {
        $this->getMailChimp()->shouldHaveType('\DrewM\MailChimp\MailChimp');
    }

    public function it_can_find_a_list_by_its_id(MailChimp $mailchimp)
    {
        $this->findById('ba039c6198')->shouldReturn(['id' => 'ba039c6198', 'name' => 'myList']);
    }

    public function it_can_not_find_a_list_by_its_id(MailChimp $mailchimp)
    {
        $mailchimp->success()->willReturn(false);
        $mailchimp->getLastResponse()->willReturn([
            "headers" => [],
            "body" => '{"type":"http://developer.mailchimp.com/documentation/mailchimp/guides/error-glossary/","title":"Invalid Resource","status":404,"detail":"The requested resource could not be found.","instance":""}'
        ]);

        $this->shouldThrow(new MailchimpException(404, 'The requested resource could not be found.', "http://developer.mailchimp.com/documentation/mailchimp/guides/error-glossary/", "Invalid Resource", null, ''))
            ->duringFindById('notfound');
    }

    public function it_subscribe_a_subscriber(MailChimp $mailchimp, $subscriber)
    {
        $this->subscribe('ba039c6198', $subscriber)->shouldReturn([
                'email_address' => 'charles@terrasse.fr',
                'merge_fields'  => ['FNAME' => 'Charles', 'LNAME' => 'Terrasse'],
                'language' => 'fr',
                'email_type'    => 'html',
                'status'        => 'subscribed'
            ]);
    }

    public function it_unsubscribe_a_subscriber(MailChimp $mailchimp, $subscriber)
    {
        $this->unsubscribe('ba039c6198', $subscriber)->shouldReturn('unsubscribed');
    }

    public function it_pending_a_subscriber(MailChimp $mailchimp, $subscriber)
    {
        $this->pending('ba039c6198', $subscriber)->shouldReturn('pending');
    }

    public function it_clean_a_subscriber(MailChimp $mailchimp, $subscriber)
    {
        $this->clean('ba039c6198', $subscriber)->shouldReturn('cleaned');
    }

    public function it_delete_a_subscriber(MailChimp $mailchimp, $subscriber)
    {
        $mailchimp->delete("lists/ba039c6198/members/b1a29fd58778c40c7f15f06a334dc691")->willReturn('deleted');

        $this->delete('ba039c6198', $subscriber)->shouldReturn('deleted');
    }

    public function it_update_a_subscriber(MailChimp $mailchimp, $subscriber)
    {
        $mailchimp->patch("lists/ba039c6198/members/b1a29fd58778c40c7f15f06a334dc691", ["email_address" => "charles@terrasse.fr", "merge_fields" => ["FNAME" => "Charles", "LNAME" => "Terrasse"], "language" => "fr", "email_type" => "html"])->willReturn('update');

        $this->update('ba039c6198', $subscriber)->shouldReturn('update');
    }

    public function it_finds_merge_tags(MailChimp $mailchimp)
    {
        $mailchimp
            ->get("lists/123/merge-fields")
            ->willReturn(
                [
                    'merge_fields' => [
                        ['tag' => 'EMAIL', 'name' => 'email'],
                        ['tag' => 'FOO', 'name' => 'Bar'],
                    ],
                    'total_items' => 2
            ]);

        $this->getMergeFields(123)->shouldReturn([['tag' => 'EMAIL', 'name' => 'email'], ['tag' => 'FOO', 'name' => 'Bar']]);
    }

    public function it_finds_merge_tags_more_than_10(MailChimp $mailchimp)
    {
        $mailchimp
            ->get("lists/123/merge-fields")
            ->willReturn(
                [
                    'merge_fields' => [
                        ['tag' => 'FOO1', 'name' => 'Bar'],
                        ['tag' => 'FOO2', 'name' => 'Bar'],
                        ['tag' => 'FOO3', 'name' => 'Bar'],
                        ['tag' => 'FOO4', 'name' => 'Bar'],
                        ['tag' => 'FOO5', 'name' => 'Bar'],
                        ['tag' => 'FOO6', 'name' => 'Bar'],
                        ['tag' => 'FOO8', 'name' => 'Bar'],
                        ['tag' => 'FOO9', 'name' => 'Bar'],
                        ['tag' => 'FOO10', 'name' => 'Bar'],
                    ],
                    'total_items' => 13
            ]);

        $mailchimp
                ->get("lists/123/merge-fields", array("count" => 13))
                ->willReturn(
                    [
                        'merge_fields' => [
                            ['tag' => 'FOO1', 'name' => 'Bar'],
                            ['tag' => 'FOO2', 'name' => 'Bar'],
                            ['tag' => 'FOO3', 'name' => 'Bar'],
                            ['tag' => 'FOO4', 'name' => 'Bar'],
                            ['tag' => 'FOO5', 'name' => 'Bar'],
                            ['tag' => 'FOO6', 'name' => 'Bar'],
                            ['tag' => 'FOO8', 'name' => 'Bar'],
                            ['tag' => 'FOO9', 'name' => 'Bar'],
                            ['tag' => 'FOO10', 'name' => 'Bar'],
                            ['tag' => 'FOO12', 'name' => 'Bar'],
                            ['tag' => 'FOO13', 'name' => 'Bar'],
                        ],
                        'total_items' => 13
                ]);

        $this->getMergeFields(123)->shouldReturn([
            ['tag' => 'FOO1', 'name' => 'Bar'],
            ['tag' => 'FOO2', 'name' => 'Bar'],
            ['tag' => 'FOO3', 'name' => 'Bar'],
            ['tag' => 'FOO4', 'name' => 'Bar'],
            ['tag' => 'FOO5', 'name' => 'Bar'],
            ['tag' => 'FOO6', 'name' => 'Bar'],
            ['tag' => 'FOO8', 'name' => 'Bar'],
            ['tag' => 'FOO9', 'name' => 'Bar'],
            ['tag' => 'FOO10', 'name' => 'Bar'],
            ['tag' => 'FOO12', 'name' => 'Bar'],
            ['tag' => 'FOO13', 'name' => 'Bar'],
        ]);
    }

    public function it_deletes_a_merge_tag(MailChimp $mailchimp)
    {
        $mailchimp->delete("lists/123/merge-fields/foo")->shouldBeCalled();

        $this->deleteMergeField(123, 'foo');
    }

    public function it_adds_a_merge_tag(MailChimp $mailchimp)
    {
        $mailchimp->post("lists/123/merge-fields", $mergeData = [
            'tag' => 'FOO',
            'name' => 'Foo bar',
            'options' => ['req' => true]
        ])->shouldBeCalled();

        $this->addMergeField(123, $mergeData);
    }

    public function it_updates_a_merge_tag(MailChimp $mailchimp)
    {
        $mailchimp->patch("lists/123/merge-fields/2", $mergeData = [
            'tag' => 'FOO',
            'name' => 'Foo bar',
            'options' => ['req' => true]
        ])->shouldBeCalled();

        $this->updateMergeField(123, 2, $mergeData);
    }

    protected function prepareSubscriber(Subscriber $subscriber)
    {
        $subscriber->getEmail()->willReturn('charles@terrasse.fr');
        $subscriber->getMergeFields()->willReturn(['FNAME' => 'Charles', 'LNAME' => 'Terrasse']);
        $subscriber->getOptions()->willReturn(['language' => 'fr', 'email_type' => 'html']);
        $subscriber->formatMailChimp()->willReturn(['email_address' => 'charles@terrasse.fr', 'merge_fields'  => ['FNAME' => 'Charles', 'LNAME' => 'Terrasse'], 'language' => 'fr', 'email_type' => 'html']);
    }

    protected function prepareMailchimpLists(MailChimp $mailchimp)
    {       
        // success
        $mailchimp->success()->willReturn(true);
        // get the list
        $mailchimp->get("lists/ba039c6198")->willReturn(['id' => 'ba039c6198', 'name' => 'myList']);
        $mailchimp->get("lists/notfound")->willReturn(null);
        // subscribe member
        $mailchimp->put("lists/ba039c6198/members/b1a29fd58778c40c7f15f06a334dc691", [
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

        $mailchimp->put("lists/ba039c6198/members/b1a29fd58778c40c7f15f06a334dc691", [
                'email_address' => 'charles@terrasse.fr',
                'merge_fields'  => ['FNAME' => 'Charles', 'LNAME' => 'Terrasse'],
                'language' => 'fr',
                'email_type'    => 'html',
                'status'  => 'unsubscribed'
            ])->willReturn('unsubscribed');

        $mailchimp->put("lists/ba039c6198/members/b1a29fd58778c40c7f15f06a334dc691", [
                'email_address' => 'charles@terrasse.fr',
                'merge_fields'  => ['FNAME' => 'Charles', 'LNAME' => 'Terrasse'],
                'language' => 'fr',
                'email_type'    => 'html',
                'status'  => 'pending'
            ])->willReturn('pending');

        $mailchimp->put("lists/ba039c6198/members/b1a29fd58778c40c7f15f06a334dc691", [
                'email_address' => 'charles@terrasse.fr',
                'merge_fields'  => ['FNAME' => 'Charles', 'LNAME' => 'Terrasse'],
                'language' => 'fr',
                'email_type'    => 'html',
                'status'  => 'cleaned'
            ])->willReturn('cleaned');
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
