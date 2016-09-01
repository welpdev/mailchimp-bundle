<?php
use PHPUnit\Framework\TestCase;
use Welp\MailchimpBundle\Subscriber\ListRepository;
use Welp\MailchimpBundle\Subscriber\Subscriber;
use \DrewM\MailChimp\MailChimp;

/**
 * These integration tests work with a test MailChimp account
 * CHANGE const with your MailChimp test account to make it work!!!
 */
class ListSynchronizerTest extends TestCase
{

    const MAILCHIMP_API_KEY = '3419ca97412af7c2893b89894275b415-us14';
    const LIST_ID = 'ba039c6198';

    protected $listRepository = null;

    public function setUp(){
        $mailchimp = new MailChimp(self::MAILCHIMP_API_KEY);
        $this->listRepository = new ListRepository($mailchimp);
    }

    public function testSynchronize()
    {

    }
}
?>
