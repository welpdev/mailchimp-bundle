<?php

namespace Welp\MailchimpBundle\Subscriber;


class ListSynchronizer
{
    protected $listRepository;

    public function __construct(ListRepository $listRepository)
    {
        $this->listRepository = $listRepository;
    }

    /**
     * Synchronise user from provider with MailChimp List
     * @param SubscriberList $list
     * @return void
     */
    public function synchronize(SubscriberList $list)
    {
        $listData = $this->listRepository->findById($list->getListId());

        // get Subscribers from the provider
        $subscribers = $list->getProvider()->getSubscribers();

        // unsubscribe difference
        $this->unsubscribeDifference($list->getListId(), $subscribers);
        // subscribe the rest
        $this->batchSubscribe($list->getListId(), $subscribers, $list->getOptions());
    }

    /**
     * Subscribe a batch of user
     * @param String $listId
     * @param Array $subscribers
     */
    protected function batchSubscribe($listId, array $subscribers = [])
    {
        $this->listRepository->batchSubscribe($listId, $subscribers);
    }

    /**
     * Unsubscribe the difference between the array subscriber an user
     * @param String $listId
     * @param array $subscribers
     */
    protected function unsubscribeDifference($listId, array $subscribers)
    {
        $mailchimpEmails = $this->listRepository->getSubscriberEmails($listId);
        $internalEmails = array_map(function(Subscriber $subscriber) {
            return $subscriber->getEmail();
        }, $subscribers);

        // emails that are present in mailchimp but not internally should be unsubscribed
        $diffenceEmails = array_diff($mailchimpEmails, $internalEmails);
        if (sizeof($diffenceEmails) == 0) {
            return;
        }

        $this->listRepository->batchUnsubscribe($listId, $diffenceEmails);
    }

    /**
     * @TODO test this, make it works
     * Synchronize Merge fields of a list and the array $mergeFields
     * @param String $listId
     * @param Array $mergeFields
     */
    public function synchronizeMergeFields($listId, array $mergeFields = [])
    {
        $mailChimpMergeFields = $this->listRepository->getMergeFields($listId);

        foreach ($mailChimpMergeFields as $tag) {
            if (!$this->tagExists($tag['tag'], $mergeFields)) {
                // tag only exist in mailchimp, we are removing it
                $this->listRepository->deleteMergeField($listId, $tag['tag']);
            }
        }

        foreach ($mergeFields as $tag) {
            // todo TAGid... refactor this for API V3
            if ($this->tagExists($tag['tag'], $mailChimpMergeFields)) {
                $this->listRepository->updateMergeField($listId, 1, $tag);
            } else {
                $this->listRepository->addMergeField($listId, $tag);
            }
        }
    }

    /**
    * @TODO test this, make it works
    */
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
