<?php

namespace spec\Welp\MailchimpBundle\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Welp\MailchimpBundle\Subscriber\ListRepository;
use Welp\MailchimpBundle\Subscriber\SubscriberList;

class ListSynchronizerSpec extends ObjectBehavior
{
    function let(ListRepository $listRepository, LoggerInterface $logger)
    {
        $this->beConstructedWith($listRepository, $logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Welp\MailchimpBundle\Subscriber\ListSynchronizer');
    }

    function it_synchronize_merge_tags($listRepository, SubscriberList $list)
    {
        $listRepository->findById('foobar')->willReturn(['id' => 123]);
        $listRepository->findMergeTags(123)->willReturn([
            ['tag' => 'TAG1', 'name' => 'Tag 1'],
            ['tag' => 'OBSOLETE', 'name' => 'This tag should not exist'],
        ]);

        $listRepository->deleteMergeTag(123, 'OBSOLETE')->shouldBeCalled();
        $listRepository->updateMergeTag(123, ['tag' => 'TAG1', 'name' => 'Tag 1', 'options' => ['req' => true]])->shouldBeCalled();
        $listRepository->addMergeTag(123, ['tag' => 'TAG2', 'name' => 'Tag 2', 'options' => ['req' => false]])->shouldBeCalled();

        $this->synchronizeMergeTags('foobar', [
            ['tag' => 'TAG1', 'name' => 'Tag 1', 'options' => ['req' => true]],
            ['tag' => 'TAG2', 'name' => 'Tag 2', 'options' => ['req' => false]],
        ]);
    }
}
