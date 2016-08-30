<?php

namespace Welp\MailchimpBundle\Subscriber;

use \DrewM\MailChimp\MailChimp;

class ListRepository
{
    const SUBSCRIBER_BATCH_SIZE = 300;

    public function __construct(MailChimp $mailchimp)
    {
        $this->mailchimp = $mailchimp;
    }

    /**
     * Find MailChimp List by list Id
     * @param String $listId
     * @return Object list http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists_list_id
     */
    public function findById($listId)
    {
        $listData = $this->mailchimp->get("lists/$listId");

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }
        return $listData;
    }

    /**
     * Subscribe a Subscriber to a list
     * @param String $listId
     * @param Subscriber $subscriber
     * @return array
     */
    public function subscribe($listId, Subscriber $subscriber)
    {
        $result = $this->mailchimp->post("lists/$listId/members", [
                'email_address' => $subscriber->getEmail(),
                'status'        => 'subscribed',
                'email_type'    => 'html',
                'merge_fields'  => $subscriber->getMergeTags()
            ]);

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result;
    }

    /**
     * Subscribe a Subscriber to a list
     * @param String $listId
     * @param Subscriber $subscriber
     */
    public function unsubscribe($listId, Subscriber $subscriber)
    {

        $subscriberHash = $this->mailchimp->subscriberHash($subscriber->getEmail());
        $result = $this->mailchimp->patch("lists/$listId/members/$subscriberHash", [
                'status'  => 'unsubscribed'
            ]);

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result;
    }

    /**
     * Delete a Subscriber to a list
     * @param String $listId
     * @param Subscriber $subscriber
     */
    public function delete($listId, Subscriber $subscriber)
    {

        $subscriberHash = $this->mailchimp->subscriberHash($subscriber->getEmail());
        $result = $this->mailchimp->delete("lists/$listId/members/$subscriberHash");

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result;
    }

    /**
     * @TODO
     * Subscribe a batch of Subscriber to a list
     * @param String $listId
     * @param Array $subscribers
     * @param Array $options
     */
    public function batchSubscribe($listId, array $subscribers, array $options = [])
    {
        $subscribers = $this->getMailchimpFormattedSubscribers($subscribers, $options);
        //@TODO

        // as suggested in MailChimp API docs, we send multiple smaller requests instead of a bigger one
        $subscriberChunks = array_chunk($subscribers, self::SUBSCRIBER_BATCH_SIZE);
        foreach ($subscriberChunks as $subscriberChunk) {
            $Batch = $this->mailchimp->new_batch();
            foreach ($subscriberChunk as $index => $newsubscribers) {
                $Batch->post("op$index", "lists/$listId/members", [
                    'email_address' => 'micky@example.com',
                    'status'        => 'subscribed',
                ]);
            }
            $result = $Batch->execute();
        }
    }

    /**
     * @TODO
     * Unsubscribe a batch of Subscriber to a list
     * @param String $listId
     * @param Array $emails
     */
    public function batchUnsubscribe($listId, array $emails)
    {
        //@TODO
        // format emails for MailChimp
        $emails = array_map(function($email) {
            return [
                'email' => $email,
            ];
        }, $emails);

        $result = $this->getDefaultResult();

        // as suggested in MailChimp API docs, we send multiple smaller requests instead of a bigger one
        $unsubscribeChunks = array_chunk($emails, self::SUBSCRIBER_BATCH_SIZE);
        foreach ($unsubscribeChunks as $unsubscribeChunk) {
            $chunkResult = $this->mailchimp->lists->batchUnsubscribe(
                $listId,
                $unsubscribeChunk,
                true, // and remove it from the list
                false // do not send goodbye email
            );

            $result['success_count'] += $chunkResult['success_count'];
            $result['error_count'] += $chunkResult['error_count'];
            $result['errors'] = array_merge($result['errors'], $chunkResult['errors']);
        }
    }

    /**
     * Get an Array of subscribers emails from a list
     * @param String $listId
     * @return Array
     */
    public function getSubscriberEmails($listId)
    {
        $emails = [];
        $result = $this->mailchimp->get("lists/$listId/members");

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        foreach ($result['members'] as $key => $member) {
            array_push($emails, $member['email_address']);
        }

        return $emails;
    }

    /**
     * find all merge tags for a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#
     * @param String $listId
     * @return Array
     */
    public function findMergeTags($listId)
    {
        $result = $this->mailchimp->get("lists/$listId/merge-fields");

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result['merge_fields'];
    }

    /**
     * delete merge tag for a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#
     * @param String $listId
     * @param String $mergeId
     * @return Array
     */
    public function deleteMergeTag($listId, $mergeId)
    {
        $result = $this->mailchimp->delete("lists/$listId/merge-fields/$mergeId");

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result;
    }

    /**
     * add merge tag for a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#
     * @param String $listId
     * @param Array $mergeData ["name" => '', "type" => '']
     * @return Array
     */
    public function addMergeTag($listId, array $mergeData)
    {
        $result = $this->mailchimp->post("lists/$listId/merge-fields", $mergeData);

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result;
    }

    /**
     * add merge tag for a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#edit-patch_lists_list_id_merge_fields_merge_id
     * @param String $listId
     * @param Array $mergeData ["name" => '', "type" => '', ...]
     * @return Array
     */
    public function updateMergeTag($listId, $mergeId, $mergeData)
    {
        $result = $this->mailchimp->patch("lists/$listId/merge-fields/$mergeId", $mergeData);

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result;
    }

    /**
     * @TODO refactor this
     * Format Subscriber for MailChimp API requests
     * @param Array $subscriber
     * @param Array $options
     * @return Array
     */
    protected function getMailchimpFormattedSubscribers(array $subscribers, array $options)
    {

        return array_map(function(Subscriber $subscriber) use ($options) {
            return [
                'email' => ['email' => $subscriber->getEmail()],
                'merge_vars' => array_merge($options, $subscriber->getMergeTags())
            ];
        }, $subscribers);
    }
}
