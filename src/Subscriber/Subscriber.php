<?php

namespace Welp\MailchimpBundle\Subscriber;

class Subscriber
{
    protected $email;
    protected $mergeTags;

    public function __construct($email, array $mergeTags = [])
    {
        $this->email = $email;
        $this->mergeTags = $mergeTags;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getMergeTags()
    {
        return $this->mergeTags;
    }
}
