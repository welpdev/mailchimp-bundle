<?php

namespace Welp\MailchimpBundle\Subscriber;

/**
 * Class to represent a subscriber
 * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
 */
class Subscriber
{
    /**
     * Subscriber's email
     * @var string
     */
    protected $email;

    /**
     * Subscriber's merge fields
     * @var array
     */
    protected $mergeFields;

    /**
     * Subscriber's options
     * @var array
     */
    protected $options;

    /**
     *
     * @param string $email
     * @param array $mergeFields
     * @param array $options
     */
    public function __construct($email, array $mergeFields = [], array $options = [])
    {
        $this->email = $email;
        $this->mergeFields = $mergeFields;
        $this->options = $options;
    }

    /**
     * Formate Subscriber for MailChimp API request
     * @return array
     */
    public function formatMailChimp()
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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Correspond to merge_fields in MailChimp request
     * @return array ['TAGKEY' => value, ...]
     */
    public function getMergeFields()
    {
        return $this->mergeFields;
    }

    /**
     * The rest of member options:
     * email_type, interests, language, vip, location, ip_signup, timestamp_signup, ip_opt, timestamp_opt
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
