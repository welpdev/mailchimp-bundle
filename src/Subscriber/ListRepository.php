<?php

namespace Welp\MailchimpBundle\Subscriber;

use DrewM\MailChimp\MailChimp;
use phpDocumentor\Reflection\Types\Void_;
use Welp\MailchimpBundle\Exception\MailchimpException;

/**
 * Handle action on MailChimp List
 */
class ListRepository
{
    /**
     * Numbers of subscribers per batch
     * @var int
     */
    const SUBSCRIBER_BATCH_SIZE = 300;

    /**
     * MailChimp count limit for result set
     * @var int
     */
    const MAILCHIMP_DEFAULT_COUNT_LIMIT = 10;

    /**
     * MailChimp Object
     * @var MailChimp
     */
    protected $mailchimp;

    /**
     * @param MailChimp $mailchimp
     */
    public function __construct(MailChimp $mailchimp)
    {
        $this->mailchimp = $mailchimp;
    }

    /**
     * Get MailChimp Object to do custom actions
     * @return MailChimp https://github.com/drewm/mailchimp-api
     */
    public function getMailChimp()
    {
        return $this->mailchimp;
    }

    /**
     * Find MailChimp List by list Id
     * @param string $listId
     * @return array list http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists_list_id
     */
    public function findById($listId)
    {
        $listData = $this->mailchimp->get("lists/$listId");

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }
        return $listData;
    }

    /**
     * core function to put (add or edit) subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     * @param string $status
     * @return array
     */
    protected function putSubscriberInList($listId, Subscriber $subscriber, $status)
    {
        if (!in_array($status, ['subscribed', 'unsubscribed', 'cleaned', 'pending', 'transactional'])) {
            throw new \RuntimeException('$status must be one of these values: subscribed, unsubscribed, cleaned, pending, transactional');
        }
        $subscriberHash = MailChimp::subscriberHash($subscriber->getEmail());
        $result = $this->mailchimp->put("lists/$listId/members/$subscriberHash",
            array_merge(
                $subscriber->formatMailChimp(),
                ['status' => $status]
            )
        );

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * Subscribe a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     * @return array
     */
    public function subscribe($listId, Subscriber $subscriber)
    {
        return $this->putSubscriberInList($listId, $subscriber, 'subscribed');
    }

    /**
     * Subscribe a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     */
    public function unsubscribe($listId, Subscriber $subscriber)
    {
        return $this->putSubscriberInList($listId, $subscriber, 'unsubscribed');
    }

    /**
     * Clean a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     */
    public function clean($listId, Subscriber $subscriber)
    {
        return $this->putSubscriberInList($listId, $subscriber, 'cleaned');
    }

    /**
     * Add/set pending a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     */
    public function pending($listId, Subscriber $subscriber)
    {
        return $this->putSubscriberInList($listId, $subscriber, 'pending');
    }

    /**
     * set transactional a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     */
    public function transactional($listId, Subscriber $subscriber)
    {
        return $this->putSubscriberInList($listId, $subscriber, 'transactional');
    }

    /**
     * Update a Subscriber to a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-patch_lists_list_id_members_subscriber_hash
     * @param string $listId
     * @param Subscriber $subscriber
     */
    public function update($listId, Subscriber $subscriber)
    {
        $subscriberHash = MailChimp::subscriberHash($subscriber->getEmail());
        $result = $this->mailchimp->patch("lists/$listId/members/$subscriberHash", $subscriber->formatMailChimp());

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * TODO not working with API V3... we can't change email of a user
     *       one possible solution is to delete old subscriber and add a new one
     *       with the same mergeFieds and Options...
     * Change email address
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-put_lists_list_id_members_subscriber_hash
     * @param string $listId
     * @param Subscriber $newSubscriber
     * @param string $oldEmailAddress
     */
    public function changeEmailAddress($listId, Subscriber $newSubscriber, $oldEmailAddress)
    {
        # @NOTE handle special cases:
        #       1. new email address already exists in List
        #       2. old email address not exists in list
        #       3. old or new email address doesn't exists in list

        $subscriberHash = MailChimp::subscriberHash($oldEmailAddress);
        $oldMember = $this->mailchimp->get("lists/$listId/members/$subscriberHash");
        if (!$this->mailchimp->success()) {
            // problem with the oldSubcriber (doest not exist, ...)
            // np we will take the new Subscriber
            $newMember = $newSubscriber->formatMailChimp();
            $newMember['status_if_new'] = 'subscribed';
        } else {
            // clean member
            unset($oldMember['_links']);
            unset($oldMember['id']);
            unset($oldMember['stats']);
            unset($oldMember['unique_email_id']);
            unset($oldMember['member_rating']);
            unset($oldMember['last_changed']);
            unset($oldMember['email_client']);
            unset($oldMember['list_id']);

            $newMember = $oldMember;
            $newMember['email_address'] = $newSubscriber->getEmail();
            $newMember['status_if_new'] = 'subscribed';

            // delete the old Member
            $deleteOld = $this->mailchimp->delete("lists/$listId/members/$subscriberHash");
            if (!$this->mailchimp->success()) {
                $this->throwMailchimpError($this->mailchimp->getLastResponse());
            }
        }

        // add/update the new member
        $subscriberHash = MailChimp::subscriberHash($newSubscriber->getEmail());
        $result = $this->mailchimp->put("lists/$listId/members/$subscriberHash", $newMember);
        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * Delete a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     */
    public function delete($listId, Subscriber $subscriber)
    {
        $subscriberHash = MailChimp::subscriberHash($subscriber->getEmail());
        $result = $this->mailchimp->delete("lists/$listId/members/$subscriberHash");

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * Subscribe a batch of Subscriber to a list
     * @param string $listId
     * @param array $subscribers
     * @return array $batchIds
     */
    public function batchSubscribe($listId, array $subscribers)
    {
        $batchResults = [];
        // as suggested in MailChimp API docs, we send multiple smaller requests instead of a bigger one
        $subscriberChunks = array_chunk($subscribers, self::SUBSCRIBER_BATCH_SIZE);
        foreach ($subscriberChunks as $subscriberChunk) {
            $Batch = $this->mailchimp->new_batch();
            foreach ($subscriberChunk as $index => $newsubscribers) {
                $subscriberHash = MailChimp::subscriberHash($newsubscribers->getEmail());
                $Batch->put("op$index", "lists/$listId/members/$subscriberHash", array_merge(
                    $newsubscribers->formatMailChimp(),
                    ['status' => 'subscribed']
                ));
            }
            $Batch->execute();
            $currentBatch = $Batch->check_status();
            array_push($batchResults, $currentBatch);
        }
        return $batchResults;
    }

    /**
     * Unsubscribe a batch of Subscriber to a list
     * @param string $listId
     * @param array $emails
     * @return array $batchIds
     */
    public function batchUnsubscribe($listId, array $emails)
    {
        $batchIds = [];
        // as suggested in MailChimp API docs, we send multiple smaller requests instead of a bigger one
        $emailsChunks = array_chunk($emails, self::SUBSCRIBER_BATCH_SIZE);
        foreach ($emailsChunks as $emailsChunk) {
            $Batch = $this->mailchimp->new_batch();
            foreach ($emailsChunk as $index => $email) {
                $emailHash = MailChimp::subscriberHash($email);
                $Batch->patch("op$index", "lists/$listId/members/$emailHash", [
                    'status' => 'unsubscribed'
                ]);
            }
            $result = $Batch->execute();
            $currentBatch = $Batch->check_status();
            array_push($batchIds, $currentBatch['id']);
        }
        return $batchIds;
    }

    /**
     * Delete a batch of Subscriber to a list
     * @param string $listId
     * @param array $emails
     * @return array $batchIds
     */
    public function batchDelete($listId, array $emails)
    {
        $batchIds = [];
        // as suggested in MailChimp API docs, we send multiple smaller requests instead of a bigger one
        $emailsChunks = array_chunk($emails, self::SUBSCRIBER_BATCH_SIZE);
        foreach ($emailsChunks as $emailsChunk) {
            $Batch = $this->mailchimp->new_batch();
            foreach ($emailsChunk as $index => $email) {
                $emailHash = MailChimp::subscriberHash($email);
                $Batch->delete("op$index", "lists/$listId/members/$emailHash");
            }
            $result = $Batch->execute();
            $currentBatch = $Batch->check_status();
            array_push($batchIds, $currentBatch['id']);
        }
        return $batchIds;
    }

    /**
     * Get Members of a list
     * @param string $listId
     * @return array
     */
    public function getMembers($listId)
    {
        $emails = [];
        $result = $this->mailchimp->get("lists/$listId/members");

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * Get an array of subscribers emails from a list
     * @param string $listId
     * @return array
     */
    public function getSubscriberEmails($listId)
    {
        $emails = [];
        $members = [];
        $offset=0;
        $maxresult = 200;
        $result = $this->mailchimp->get("lists/$listId/members", ['count'=> $maxresult]);

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        $totalItems = $result['total_items'];
        $members = array_merge($members, $result['members']);

        while ($offset < $totalItems) {
            $offset+=$maxresult;
            $result = $this->mailchimp->get("lists/$listId/members", [
                        'count'         => $maxresult,
                        'offset'        => $offset
                    ]);

            if (!$this->mailchimp->success()) {
                $this->throwMailchimpError($this->mailchimp->getLastResponse());
            }
            $members = array_merge($members, $result['members']);
        };

        foreach ($members as $key => $member) {
            array_push($emails, $member['email_address']);
        }

        return $emails;
    }

    /**
     * find all merge fields for a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#
     * @param string $listId
     * @return array
     */
    public function getMergeFields($listId)
    {
        $result = $this->mailchimp->get("lists/$listId/merge-fields");

        # Handle mailchimp default count limit
        if ($result['total_items'] > self::MAILCHIMP_DEFAULT_COUNT_LIMIT) {
            $result = $this->mailchimp->get("lists/$listId/merge-fields", array("count" => $result['total_items']));
        }

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result['merge_fields'];
    }

    /**
     * add merge field for a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#
     * @param string $listId
     * @param array $mergeData ["name" => '', "type" => '']
     * @return array
     */
    public function addMergeField($listId, array $mergeData)
    {
        $result = $this->mailchimp->post("lists/$listId/merge-fields", $mergeData);

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * add merge field for a list
     * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#edit-patch_lists_list_id_merge_fields_merge_id
     * @param string $listId
     * @param string $mergeId
     * @param array $mergeData ["name" => '', "type" => '', ...]
     * @return array
     */
    public function updateMergeField($listId, $mergeId, $mergeData)
    {
        $result = $this->mailchimp->patch("lists/$listId/merge-fields/$mergeId", $mergeData);

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
    * delete merge field for a list
    * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#
    * @param string $listId
    * @param string $mergeId
    * @return array
    */
    public function deleteMergeField($listId, $mergeId)
    {
        $result = $this->mailchimp->delete("lists/$listId/merge-fields/$mergeId");

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
    * Automatically configure Webhook for a list
    * @param string $listId
    * @param string $webhookurl
    * @return array
    */
    public function registerMainWebhook($listId, $webhookurl)
    {
        // Configure webhook
        $subscribeWebhook = [
            'url' => $webhookurl,
            'events' => [
                'subscribe'   => true,
                'unsubscribe' => true,
                'profile'     => true,
                'cleaned'     => true,
                'upemail'     => true,
                'campaign'    => true
            ],
            'sources' => [
                'user'  => true,
                'admin' => true,
                'api'   => false // to avoid double (infinite loop) update (update an subscriber with the API and the webhook reupdate the user, ...)
            ]
        ];

        return $this->addWebhook($listId, $subscribeWebhook);
    }

    /**
    * Add a new webhook to a list
    * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/webhooks/#
    * @param string $listId
    * @param array $webhookData
    * @return array
    */
    public function addWebhook($listId, array $webhookData)
    {
        $result = $this->mailchimp->post("lists/$listId/webhooks", $webhookData);

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
    * Get webhooks of a list
    * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/webhooks/#
    * @param string $listId
    * @return array
    */
    public function getWebhooks($listId)
    {
        $result = $this->mailchimp->get("lists/$listId/webhooks");

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * [throwMailchimpError description]
     * @param  array  $errorResponse [description]
     * @return void
     * @throws MailchimpException [description]
     */
    private function throwMailchimpError(array $errorResponse)
    {
        $errorArray = json_decode($errorResponse['body'], true);
        if (is_array($errorArray) && array_key_exists('errors', $errorArray)) {
            throw new MailchimpException(
                $errorArray['status'],
                $errorArray['detail'],
                $errorArray['type'],
                $errorArray['title'],
                $errorArray['errors'],
                $errorArray['instance']
            );
        } else {
            throw new MailchimpException(
                $errorArray['status'],
                $errorArray['detail'],
                $errorArray['type'],
                $errorArray['title'],
                null,
                $errorArray['instance']
            );
        }
    }
}
