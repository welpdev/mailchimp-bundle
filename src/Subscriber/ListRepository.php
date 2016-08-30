<?php

namespace Welp\MailchimpBundle\Subscriber;

use \DrewM\MailChimp\MailChimp;

class ListRepository
{
    const SUBSCRIBER_BATCH_SIZE = 500;

    public function __construct(MailChimp $mailchimp)
    {
        $this->mailchimp = $mailchimp;
    }

    public function findById($listId)
    {
        $listData = $this->mailchimp->get("lists/$listId");

        /*if(!$this->mailchimp->success()){
            throw new \RuntimeException($this->mailchimp->getLastError());
        }*/

        return $listData;
    }

    public function subscribe($listId, Subscriber $subscriber)
    {
        $result = $MailChimp->post("lists/$listId/members", [
                'email_address' => $subscriber->getEmail(),
                'status'        => 'subscribed',
                'email_type'    => 'html',
                'merge_fields'  => $subscriber->getMergeTags()
            ]);
    }

    public function batchSubscribe($listId, array $subscribers, array $options = [])
    {
        $subscribers = $this->getMailchimpFormattedSubscribers($subscribers, $options);

        $result = $this->getDefaultResult();

        // as suggested in MailChimp API docs, we send multiple smaller requests instead of a bigger one
        $subscriberChunks = array_chunk($subscribers, self::SUBSCRIBER_BATCH_SIZE);
        foreach ($subscriberChunks as $subscriberChunk) {
            $chunkResult = $this->mailchimp->lists->batchSubscribe(
                $listId,
                $subscriberChunk,
                false, // do not use dual optin (to prevent sending another confirmation e-mail)
                true // do update the subscriber if it already exists
            );

            $result['add_count'] += $chunkResult['add_count'];
            $result['update_count'] += $chunkResult['update_count'];
            $result['error_count'] += $chunkResult['error_count'];
            $result['errors'] = array_merge($result['errors'], $chunkResult['errors']);
        }
    }

    public function unsubscribe($listId, Subscriber $subscriber)
    {
        $this->mailchimp->lists->unsubscribe(
            $listId,
            ['email' => $subscriber->getEmail()],
            true, // and remove it from the list
            false, // do not send goodbye email
            false // do not send notify
        );
    }

    public function batchUnsubscribe($listId, array $emails)
    {
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

    public function getSubscriberEmails(array $listData)
    {
        $emails = [];
        $memberCount = $listData['stats']['member_count'];

        $page = 0;
        $limit = 100;
        while ($page * $limit < $memberCount) {
            $members = $this->mailchimp->lists->members($listData['id'], 'subscribed', [
                'start' => $page,
                'limit' => $limit
            ]);

            $emails = array_merge($emails, array_map(function($data) {
                return $data['email'];
            }, $members['data']));

            $page++;
        }

        return $emails;
    }

    public function findMergeTags($listId)
    {
        $result = $this->mailchimp->lists->mergeVars([$listId]);
        if (!isset($result['data'][0]['merge_vars'])) {
            throw new \RuntimeException(sprintf('Could not find merge tags for list "%s".', $listId));
        }

        $tags = $result['data'][0]['merge_vars'];
        $tags = array_filter($tags, function($tag) {
            // we exclude the EMAIL tag that can't be worked on
            return $tag['tag'] !== 'EMAIL';
        });

        return array_values($tags);
    }

    public function deleteMergeTag($listId, $tag)
    {
        $this->mailchimp->lists->mergeVarDel($listId, $tag);
    }

    public function addMergeTag($listId, array $tag)
    {
        $this->mailchimp->lists->mergeVarAdd($listId, $tag['tag'], $tag['name'], $tag['options']);
    }

    public function updateMergeTag($listId, array $tag)
    {
        $tag['options']['name'] = $tag['name'];
        unset($tag['options']['field_type']);

        $this->mailchimp->lists->mergeVarUpdate($listId, $tag['tag'], $tag['options']);
    }

    protected function getMailchimpFormattedSubscribers(array $subscribers, array $options)
    {
        return array_map(function(Subscriber $subscriber) use ($options) {
            return [
                'email' => ['email' => $subscriber->getEmail()],
                'merge_vars' => array_merge($options, $subscriber->getMergeTags())
            ];
        }, $subscribers);
    }

    protected function getDefaultResult()
    {
        return [
        ];
    }
}
