<?php

namespace Welp\MailchimpBundle\Subscriber;

use Mailchimp;
use Psr\Log\LoggerInterface;

class ListRepository
{
    const SUBSCRIBER_BATCH_SIZE = 500;

    public function __construct(Mailchimp $mailchimp, LoggerInterface $logger)
    {
        $this->mailchimp = $mailchimp;
        $this->logger = $logger;
    }

    public function findByName($name)
    {
        $listData = $this->mailchimp->lists->getList([
            'list_name' => $name
        ]);

        if (
            !isset($listData['total']) || 
            $listData['total'] === 0
        ) {
            throw new \RuntimeException(sprintf('The list "%s" was not found in Mailchimp. You need to create it first in Mailchimp backend.', $name));
        }

        if (!isset($listData['data'][0]['id'])) {
            throw new \RuntimeException('List id could not be found.');
        }

        return $listData['data'][0];
    }

    public function subscribe($listId, Subscriber $subscriber)
    {
        $this->mailchimp->lists->subscribe(
            $listId,
            ['email' => $subscriber->getEmail()],
            $subscriber->getMergeTags(),
            'html', // email preference
            false, // do not use dual optin (to prevent sending another confirmation e-mail)
            true // do update the subscriber if it already exists
        );
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

        $this->logResult($result);
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

        $this->logResult($result);
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

        $this->logger->info(sprintf('Tag "%s" has been removed from MailChimp.', $tag));
    }

    public function addMergeTag($listId, array $tag)
    {
        $this->mailchimp->lists->mergeVarAdd($listId, $tag['tag'], $tag['name'], $tag['options']);

        $this->logger->info(sprintf('Tag "%s" has been added to MailChimp.', $tag['name']));
    }

    public function updateMergeTag($listId, array $tag)
    {
        $tag['options']['name'] = $tag['name'];
        unset($tag['options']['field_type']);

        $this->mailchimp->lists->mergeVarUpdate($listId, $tag['tag'], $tag['options']);

        $this->logger->info(sprintf('Tag "%s" has been updated in MailChimp.', $tag['name']));
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

    protected function logResult(array $result = [])
    {
        if ($result['add_count'] > 0) {
            $this->logger->info(sprintf('%s subscribers added.', $result['add_count']));
        }

        if ($result['update_count'] > 0) {
            $this->logger->info(sprintf('%s subscribers updated.', $result['update_count']));
        }

        if ($result['success_count'] > 0) {
            $this->logger->info(sprintf('%s emails unsubscribed from mailchimp.', $result['success_count']));
        }

        if ($result['error_count'] > 0) {
            $this->logger->error(sprintf('%s subscribers errored.', $result['error_count']));
            foreach ($result['errors'] as $error) {
                $this->logger->error(sprintf('Subscriber "%s" has not been processed: "%s"', $error['email']['email'], $error['error']));
            }
        }
    }

    protected function getDefaultResult()
    {
        return [
            'add_count' => 0,
            'update_count' => 0,
            'error_count' => 0,
            'success_count' => 0,
            'errors' => []
        ];
    }
}
