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
        $result = $this->mailchimp->post("lists/$listId/members",
            array_merge(
                $subscriber->formatMailChimp(),
                ['status' => 'subscribed']
            )
        );

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result;
    }

    /**
     * Update a Subscriber to a list
     * @param String $listId
     * @param Subscriber $subscriber
     */
    public function update($listId, Subscriber $subscriber)
    {

        $subscriberHash = $this->mailchimp->subscriberHash($subscriber->getEmail());
        $result = $this->mailchimp->patch("lists/$listId/members/$subscriberHash", $subscriber->formatMailChimp());

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
    public function batchSubscribe($listId, array $subscribers)
    {
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
     * find all merge fields for a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#
     * @param String $listId
     * @return Array
     */
    public function getMergeFields($listId)
    {
        $result = $this->mailchimp->get("lists/$listId/merge-fields");

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result['merge_fields'];
    }

    /**
     * add merge field for a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#
     * @param String $listId
     * @param Array $mergeData ["name" => '', "type" => '']
     * @return Array
     */
    public function addMergeField($listId, array $mergeData)
    {
        $result = $this->mailchimp->post("lists/$listId/merge-fields", $mergeData);

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result;
    }

    /**
     * add merge field for a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#edit-patch_lists_list_id_merge_fields_merge_id
     * @param String $listId
     * @param Array $mergeData ["name" => '', "type" => '', ...]
     * @return Array
     */
    public function updateMergeField($listId, $mergeId, $mergeData)
    {
        $result = $this->mailchimp->patch("lists/$listId/merge-fields/$mergeId", $mergeData);

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result;
    }

    /**
    * delete merge field for a list
    * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#
    * @param String $listId
    * @param String $mergeId
    * @return Array
    */
    public function deleteMergeField($listId, $mergeId)
    {
        $result = $this->mailchimp->delete("lists/$listId/merge-fields/$mergeId");

        if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }

        return $result;
    }
}
