<?php

namespace spec\Welp\MailchimpBundle\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Welp\MailchimpBundle\Subscriber\ListRepository;
use Welp\MailchimpBundle\Subscriber\SubscriberList;

class ListSynchronizerSpec extends ObjectBehavior
{
    function let(ListRepository $listRepository)
    {
        $this->beConstructedWith($listRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Subscriber\ListSynchronizer');
    }

    function it_synchronize_merge_tags($listRepository, SubscriberList $list)
    {
        $listRepository->findById('foobar')->willReturn(['id' => 'foobar']);
        $listRepository->getMergeFields('foobar')->willReturn([
            ['merge_id' => 1, 'tag' => 'TAG1', 'name' => 'Tag 1'],
            ['merge_id' => 2, 'tag' => 'OBSOLETE', 'name' => 'This tag should not exist'],
        ]);

        $listRepository->deleteMergeField('foobar', 2)->shouldBeCalled();
        $listRepository->updateMergeField('foobar', 1, ['tag' => 'TAG1', 'name' => 'Tag 1', 'options' => ['req' => true]])->shouldBeCalled();
        $listRepository->addMergeField('foobar', ['tag' => 'TAG2', 'name' => 'Tag 2', 'options' => ['req' => false]])->shouldBeCalled();

        $this->synchronizeMergeFields('foobar', [
            ['tag' => 'TAG1', 'name' => 'Tag 1', 'options' => ['req' => true]],
            ['tag' => 'TAG2', 'name' => 'Tag 2', 'options' => ['req' => false]],
        ]);
    }
}
