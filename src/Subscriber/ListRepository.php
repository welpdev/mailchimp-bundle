<?php

namespace Welp\MailchimpBundle\Subscriber;

use DrewM\MailChimp\MailChimp;
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
    public const SUBSCRIBER_BATCH_SIZE = 300;

    /**
     * MailChimp count limit for result set
     * @var int
     */
    public const MAILCHIMP_DEFAULT_COUNT_LIMIT = 10;

    /**
     * MailChimp Object
     * @var MailChimp
     */
    protected MailChimp $mailchimp;

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
    public function getMailChimp(): MailChimp
    {
        return $this->mailchimp;
    }

    /**
     * Find MailChimp List by list ID
     * @param string $listId
     * @return array list https://mailchimp.com/developer/marketing/api/lists/get-list-info/
     * @throws MailchimpException
     */
    public function findById(string $listId): array
    {
        $listData = $this->mailchimp->get('lists/' . $listId);

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
     * @throws MailchimpException
     */
    protected function putSubscriberInList(string $listId, Subscriber $subscriber, string $status): array
    {
        if (!in_array($status, ['subscribed', 'unsubscribed', 'cleaned', 'pending', 'transactional'])) {
            throw new \RuntimeException('$status must be one of these values: subscribed, unsubscribed, cleaned, pending, transactional');
        }

        $subscriberHash = MailChimp::subscriberHash($subscriber->getEmail());

        $result = $this->mailchimp->put('lists/' . $listId . '/members/' . $subscriberHash,
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
     * @throws MailchimpException
     */
    public function subscribe(string $listId, Subscriber $subscriber): array
    {
        return $this->putSubscriberInList($listId, $subscriber, 'subscribed');
    }

    /**
     * Subscribe a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     * @throws MailchimpException
     */
    public function unsubscribe(string $listId, Subscriber $subscriber): array
    {
        return $this->putSubscriberInList($listId, $subscriber, 'unsubscribed');
    }

    /**
     * Clean a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     * @return array
     * @throws MailchimpException
     */
    public function clean(string $listId, Subscriber $subscriber): array
    {
        return $this->putSubscriberInList($listId, $subscriber, 'cleaned');
    }

    /**
     * Add/set pending a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     * @return array
     * @throws MailchimpException
     */
    public function pending(string $listId, Subscriber $subscriber): array
    {
        return $this->putSubscriberInList($listId, $subscriber, 'pending');
    }

    /**
     * set transactional a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     * @return array
     * @throws MailchimpException
     */
    public function transactional(string $listId, Subscriber $subscriber): array
    {
        return $this->putSubscriberInList($listId, $subscriber, 'transactional');
    }

    /**
     * Update a Subscriber to a list
     * https://mailchimp.com/developer/marketing/api/list-members/update-list-member/
     * @param string $listId
     * @param Subscriber $subscriber
     * @return array|bool
     * @throws MailchimpException
     */
    public function update(string $listId, Subscriber $subscriber): bool|array
    {
        $subscriberHash = MailChimp::subscriberHash($subscriber->getEmail());
        $result = $this->mailchimp->patch('lists/' . $listId . '/members/' . $subscriberHash, $subscriber->formatMailChimp());

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
     * https://mailchimp.com/developer/marketing/api/list-members/update-list-member/
     * @param string $listId
     * @param Subscriber $newSubscriber
     * @param string $oldEmailAddress
     * @return array|bool
     * @throws MailchimpException
     */
    public function changeEmailAddress(string $listId, Subscriber $newSubscriber, string $oldEmailAddress): bool|array
    {
        # @NOTE handle special cases:
        #       1. new email address already exists in List
        #       2. old email address not exists in list
        #       3. old or new email address doesn't exist in list

        $subscriberHash = MailChimp::subscriberHash($oldEmailAddress);
        $oldMember = $this->mailchimp->get('lists/' . $listId . '/members/' . $subscriberHash);

        if (!$this->mailchimp->success()) {
            // problem with the oldSubcriber (doest not exist, ...)
            // np we will take the new Subscriber
            $newMember = $newSubscriber->formatMailChimp();
            $newMember['status_if_new'] = 'subscribed';
        } else {
            // clean member
            unset($oldMember['_links'], $oldMember['id'], $oldMember['stats'], $oldMember['unique_email_id'], $oldMember['member_rating'], $oldMember['last_changed'], $oldMember['email_client'], $oldMember['list_id']);

            $newMember = $oldMember;
            $newMember['email_address'] = $newSubscriber->getEmail();
            $newMember['status_if_new'] = 'subscribed';

            // delete the old Member
            $deleteOld = $this->mailchimp->delete('lists/' . $listId . '/members/' . $subscriberHash);

            if (!$this->mailchimp->success()) {
                $this->throwMailchimpError($this->mailchimp->getLastResponse());
            }
        }

        // add/update the new member
        $subscriberHash = MailChimp::subscriberHash($newSubscriber->getEmail());
        $result = $this->mailchimp->put('lists/' . $listId . '/members/' . $subscriberHash, $newMember);

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * Delete a Subscriber to a list
     * @param string $listId
     * @param Subscriber $subscriber
     * @return array|bool
     * @throws MailchimpException
     */
    public function delete(string $listId, Subscriber $subscriber): bool|array
    {
        $subscriberHash = MailChimp::subscriberHash($subscriber->getEmail());
        $result = $this->mailchimp->delete('lists/' . $listId . '/members/' . $subscriberHash);

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
    public function batchSubscribe(string $listId, array $subscribers): array
    {
        $batchResults = [];
        // as suggested in MailChimp API docs, we send multiple smaller requests instead of a bigger one
        $subscriberChunks = array_chunk($subscribers, self::SUBSCRIBER_BATCH_SIZE);

        foreach ($subscriberChunks as $subscriberChunk) {
            $batch = $this->mailchimp->new_batch();

            foreach ($subscriberChunk as $index => $newsubscribers) {
                $subscriberHash = MailChimp::subscriberHash($newsubscribers->getEmail());

                $batch->put(
                    'op' . $index,
                    'lists/' . $listId . '/members/' . $subscriberHash,
                    array_merge(
                        $newsubscribers->formatMailChimp(),
                        ['status' => 'subscribed']
                    )
                );
            }
            $batch->execute();
            $currentBatch = $batch->check_status();
            $batchResults[] = $currentBatch; // faster than array_push() here
        }

        return $batchResults;
    }

    /**
     * Unsubscribe a batch of Subscriber to a list
     * @param string $listId
     * @param array $emails
     * @return array $batchIds
     */
    public function batchUnsubscribe(string $listId, array $emails): array
    {
        $batchIds = [];
        // as suggested in MailChimp API docs, we send multiple smaller requests instead of a bigger one
        $emailsChunks = array_chunk($emails, self::SUBSCRIBER_BATCH_SIZE);

        foreach ($emailsChunks as $emailsChunk) {
            $batch = $this->mailchimp->new_batch();

            foreach ($emailsChunk as $index => $email) {
                $emailHash = MailChimp::subscriberHash($email);
                $batch->patch('op' . $index, 'lists/' . $listId . '/members/' . $emailHash, [
                    'status' => 'unsubscribed'
                ]);
            }

            $batch->execute();
            $currentBatch = $batch->check_status();

            $batchIds[] = $currentBatch['id']; // faster than array_push() here
        }

        return $batchIds;
    }

    /**
     * Delete a batch of Subscriber to a list
     * @param string $listId
     * @param array $emails
     * @return array $batchIds
     */
    public function batchDelete(string $listId, array $emails): array
    {
        $batchIds = [];
        // as suggested in MailChimp API docs, we send multiple smaller requests instead of a bigger one
        $emailsChunks = array_chunk($emails, self::SUBSCRIBER_BATCH_SIZE);

        foreach ($emailsChunks as $emailsChunk) {
            $batch = $this->mailchimp->new_batch();

            foreach ($emailsChunk as $index => $email) {
                $emailHash = MailChimp::subscriberHash($email);
                $batch->delete('op' . $index, 'lists/' . $listId . '/members/' . $emailHash);
            }

            $batch->execute();
            $currentBatch = $batch->check_status();
            $batchIds[] = $currentBatch['id'];
        }
        return $batchIds;
    }

    /**
     * Get Members of a list
     * @param string $listId
     * @return array
     * @throws MailchimpException
     */
    public function getMembers(string $listId): array
    {
        $result = $this->mailchimp->get('lists/' . $listId . '/members');

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * Get an array of subscribers emails from a list
     * @param string $listId
     * @return array
     * @throws MailchimpException
     */
    public function getSubscriberEmails(string $listId): array
    {
        $emails = [];
        $members = [];
        $offset = 0;
        $maxresult = 200;
        $result = $this->mailchimp->get('lists/' . $listId . '/members', ['count' => $maxresult]);

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        $totalItems = $result['total_items'];
        $members = array_merge($members, $result['members']);

        while ($offset < $totalItems) {
            $offset += $maxresult;
            $result = $this->mailchimp->get('lists/' . $listId . '/members', [
                'count' => $maxresult,
                'offset' => $offset
            ]);

            if (!$this->mailchimp->success()) {
                $this->throwMailchimpError($this->mailchimp->getLastResponse());
            }

            $members = array_merge($members, $result['members']);
        }

        foreach ($members as $key => $member) {
            $emails[] = $member['email_address'];
        }

        return $emails;
    }

    /**
     * find all merge fields for a list
     * https://mailchimp.com/developer/marketing/api/list-merges/
     * @param string $listId
     * @return array
     * @throws MailchimpException
     */
    public function getMergeFields(string $listId): array
    {
        $result = $this->mailchimp->get('lists/' . $listId . '/merge-fields');

        # Handle mailchimp default count limit
        if ($result['total_items'] > self::MAILCHIMP_DEFAULT_COUNT_LIMIT) {
            $result = $this->mailchimp->get('lists/' . $listId . '/merge-fields', ['count' => $result['total_items']]);
        }

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result['merge_fields'];
    }

    /**
     * add merge field for a list
     * https://mailchimp.com/developer/marketing/api/list-merges/
     * @param string $listId
     * @param array $mergeData ["name" => '', "type" => '']
     * @return array
     * @throws MailchimpException
     */
    public function addMergeField(string $listId, array $mergeData): array
    {
        $result = $this->mailchimp->post('lists/' . $listId . '/merge-fields', $mergeData);

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * add merge field for a list
     * https://mailchimp.com/developer/marketing/api/list-merges/
     * @param string $listId
     * @param string $mergeId
     * @param array $mergeData ["name" => '', "type" => '', ...]
     * @return array
     * @throws MailchimpException
     */
    public function updateMergeField(string $listId, string $mergeId, array $mergeData): array
    {
        $result = $this->mailchimp->patch('lists/' . $listId . '/merge-fields/' . $mergeId, $mergeData);

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * delete merge field for a list
     * https://mailchimp.com/developer/marketing/api/list-merges/
     * @param string $listId
     * @param string $mergeId
     * @return array
     * @throws MailchimpException
     */
    public function deleteMergeField(string $listId, string $mergeId): array
    {
        $result = $this->mailchimp->delete('lists/' . $listId . '/merge-fields/' . $mergeId);

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
     * @throws MailchimpException
     */
    public function registerMainWebhook(string $listId, string $webhookurl): array
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
                'api'   => false // to avoid double (infinite loop) update (update a subscriber with the API and the webhook reupdate the user, ...)
            ]
        ];

        return $this->addWebhook($listId, $subscribeWebhook);
    }

    /**
     * Add a new webhook to a list
     * https://mailchimp.com/developer/marketing/api/list-webhooks/
     * @param string $listId
     * @param array $webhookData
     * @return array
     * @throws MailchimpException
     */
    public function addWebhook(string $listId, array $webhookData): array
    {
        $result = $this->mailchimp->post('lists/' . $listId . '/webhooks', $webhookData);

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * Get webhooks of a list
     * https://mailchimp.com/developer/marketing/api/list-webhooks/
     * @param string $listId
     * @return array
     * @throws MailchimpException
     */
    public function getWebhooks(string $listId): array
    {
        $result = $this->mailchimp->get('lists/' . $listId . '/webhooks');

        if (!$this->mailchimp->success()) {
            $this->throwMailchimpError($this->mailchimp->getLastResponse());
        }

        return $result;
    }

    /**
     * [throwMailchimpError description]
     * @param array $errorResponse [description]
     * @return void
     * @throws MailchimpException [description]
     */
    private function throwMailchimpError(array $errorResponse): void
    {
        try {
            $errorArray = json_decode($errorResponse['body'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new MailchimpException(
                400,
                $e->getMessage(),
                'JSON',
                'Invalid JSON response.',
            );
        }

        if (is_array($errorArray) && array_key_exists('errors', $errorArray)) {
            throw new MailchimpException(
                $errorArray['status'],
                $errorArray['detail'],
                $errorArray['type'],
                $errorArray['title'],
                $errorArray['errors'],
                $errorArray['instance']
            );
        }

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
