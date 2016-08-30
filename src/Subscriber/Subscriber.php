<?php

namespace Welp\MailchimpBundle\Subscriber;

/**
* http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
*/
class Subscriber
{
    protected $email;
    protected $mergeFields;
    protected $options;

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
    public function formatMailChimp(){
        return array_merge([
            'email_address' => $this->getEmail(),
            'merge_fields'  => $this->getMergeFields()
        ], $this->options);
    }

    /**
     * Correspond to email_address in MailChimp request
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Correspond to merge_fields in MailChimp request
     * Array ['TAGKEY' => value, ...]
     */
    public function getMergeFields()
    {
        return $this->mergeFields;
    }

    /**
     * The rest of member options:
     * email_type, interests, language, vip, location, ip_signup, timestamp_signup, ip_opt, timestamp_opt
     */
    public function getOptions()
    {
        return $this->options;
    }
}
