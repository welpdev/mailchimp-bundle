<?php

namespace Welp\MailchimpBundle\Subscriber;

/**
 * Handle Synchronization between SubscriberList and specific MailChimp List
 */
class ListSynchronizer
{
    /**
     * @var ListRepository
     */
    protected $listRepository;

    /**
     * @param ListRepository $listRepository
     */
    public function __construct(ListRepository $listRepository)
    {
        $this->listRepository = $listRepository;
    }

    /**
     * Synchronise user from provider with MailChimp List
     * @param SubscriberListInterface $list
     * @return void
     */
    public function synchronize(SubscriberListInterface $list)
    {
        $listData = $this->listRepository->findById($list->getListId());

        // get Subscribers from the provider
        $subscribers = $list->getProvider()->getSubscribers();

        // unsubscribe difference
        $this->unsubscribeDifference($list->getListId(), $subscribers);
        // subscribe the rest
        return $this->batchSubscribe($list->getListId(), $subscribers);
    }

    /**
     * Subscribe a batch of user
     * @param string $listId
     * @param array $subscribers
     * @return void
     */
    protected function batchSubscribe($listId, array $subscribers = [])
    {
        return $this->listRepository->batchSubscribe($listId, $subscribers);
    }

    /**
     * Unsubscribe the difference between the array subscriber an user
     * @param string $listId
     * @param array $subscribers
     * @return void
     */
    protected function unsubscribeDifference($listId, array $subscribers)
    {
        $mailchimpEmails = $this->listRepository->getSubscriberEmails($listId);
        $internalEmails = array_map(function (Subscriber $subscriber) {
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
     * Synchronize Merge fields of a list and the array $mergeFields
     * @param string $listId
     * @param array $mergeFields
     * @return void
     */
    public function synchronizeMergeFields($listId, array $mergeFields = [])
    {
        $mailChimpMergeFields = $this->listRepository->getMergeFields($listId);

        foreach ($mailChimpMergeFields as $tag) {
            if (!$this->tagExists($tag['tag'], $mergeFields)) {
                // tag only exist in mailchimp, we are removing it
                $this->listRepository->deleteMergeField($listId, $tag['merge_id']);
            }
        }

        foreach ($mergeFields as $tag) {
            if ($tagId = $this->tagExists($tag['tag'], $mailChimpMergeFields)) {
                // update mergeField in mailChimp
                $this->listRepository->updateMergeField($listId, $tagId, $tag);
            } else {
                $this->listRepository->addMergeField($listId, $tag);
            }
        }
    }

    /**
    * Test if the merge field Tag exists in an array
    * @param string $tagName
    * @param array $tags
    * @return mixed (Boolean true|false) or $tag['merge_id']
    */
    protected function tagExists($tagName, array $tags)
    {
        foreach ($tags as $tag) {
            if ($tag['tag'] == $tagName) {
                if (array_key_exists('merge_id', $tag)) {
                    return $tag['merge_id'];
                }
                return true;
            }
        }
        return false;
    }
}
