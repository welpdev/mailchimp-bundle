<?php

namespace Welp\MailchimpBundle\Subscriber;


class ListSynchronizer
{
    protected $listRepository;

    public function __construct(ListRepository $listRepository)
    {
        $this->listRepository = $listRepository;
    }

    public function synchronize(SubscriberList $list)
    {
        $listData = $this->listRepository->findById($list->getListId());

        $subscribers = $list->getProvider()->getSubscribers();

        $this->unsubscribeDifference($listData, $subscribers);
        $this->batchSubscribe($listData, $subscribers, $list->getOptions());
    }

    protected function batchSubscribe(array $listData, array $subscribers = [], array $options = [])
    {
        $this->listRepository->batchSubscribe($listData['id'], $subscribers, $options);
    }

    protected function unsubscribeDifference(array $listData, array $subscribers)
    {
        $mailchimpEmails = $this->listRepository->getSubscriberEmails($listData);
        $internalEmails = array_map(function(Subscriber $subscriber) {
            return $subscriber->getEmail();
        }, $subscribers);

        // emails that are present in mailchimp but not internally should be unsubscribed
        $diffenceEmails = array_diff($mailchimpEmails, $internalEmails);
        if (sizeof($diffenceEmails) == 0) {
            return;
        }

        $this->listRepository->batchUnsubscribe($listData['id'], $diffenceEmails);
    }

    public function synchronizeMergeTags($listId, array $mergeTags = [])
    {
        $listData = $this->listRepository->findById($listId);
        $listId = $listData['id'];

        $mailChimpMergeTags = $this->listRepository->findMergeTags($listId);

        foreach ($mailChimpMergeTags as $tag) {
            if (!$this->tagExists($tag['tag'], $mergeTags)) {
                // tag only exist in mailchimp, we are removing it
                $this->listRepository->deleteMergeTag($listId, $tag['tag']);
            }
        }

        foreach ($mergeTags as $tag) {
            if ($this->tagExists($tag['tag'], $mailChimpMergeTags)) {
                $this->listRepository->updateMergeTag($listId, $tag);
            } else {
                $this->listRepository->addMergeTag($listId, $tag);
            }
        }
    }

    protected function tagExists($tagName, array $tags)
    {
        foreach ($tags as $tag) {
            if ($tag['tag'] == $tagName) {
                return true;
            }
        }

        return false;
    }
}
