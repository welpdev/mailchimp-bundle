<?php

namespace Welp\MailchimpBundle\Subscriber;

/**
 * Class to represent a subscriber
 * https://mailchimp.com/developer/marketing/api/list-members/
 */
class Subscriber
{
    /**
     * Subscriber's email
     * @var string
     */
    protected string $email;

    /**
     * Subscriber's merge fields
     * @var array
     */
    protected array $mergeFields;

    /**
     * Subscriber's options
     * @var array
     */
    protected array $options;

    /**
     *
     * @param string $email
     * @param array $mergeFields
     * @param array $options
     */
    public function __construct(string $email, array $mergeFields = [], array $options = [])
    {
        $this->email = $email;
        $this->mergeFields = $mergeFields;
        $this->options = $options;
    }

    /**
     * Format Subscriber for MailChimp API request
     * @return array
     */
    public function formatMailChimp(): array
    {
        $options = $this->options;
        if (!empty($this->getMergeFields())) {
            $options = array_merge([
                'merge_fields' => $this->getMergeFields()
            ], $options);
        }

        return array_merge([
            'email_address' => $this->getEmail()
        ], $options);
    }

    /**
     * Correspond to email_address in MailChimp request
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set mergefields
     * @param array $mergeFields
     * @return array ['TAGKEY' => value, ...]
     */
    public function setMergeFields(array $mergeFields): array
    {
        // since feb2017, MailChimp API doesn't handle null value en throw 400
        // errors when you try to add subscriber with a mergefields value to null
        foreach ($mergeFields as $key => $value) {
            if ($value === null) {
                unset($mergeFields[$key]);
            }
        }
        $this->mergeFields = $mergeFields;

        return $this->mergeFields;
    }

    /**
     * Correspond to merge_fields in MailChimp request
     * @return array ['TAGKEY' => value, ...]
     */
    public function getMergeFields(): array
    {
        // since fev2017, MailChimp API doesn't handle null value en throw 400
        // errors when you try to add subscriber with a mergefields value to null
        foreach ($this->mergeFields as $key => $value) {
            if ($value === null) {
                unset($this->mergeFields[$key]);
            }
        }

        return $this->mergeFields;
    }

    /**
     * The rest of member options:
     * email_type, interests, language, vip, location, ip_signup, timestamp_signup, ip_opt, timestamp_opt
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
