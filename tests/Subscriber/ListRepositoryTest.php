<?php
use PHPUnit\Framework\TestCase;
use Welp\MailchimpBundle\Subscriber\ListRepository;
use Welp\MailchimpBundle\Subscriber\Subscriber;
use \DrewM\MailChimp\MailChimp;

/**
 * These integration tests work with a test MailChimp account
 * CHANGE const with your MailChimp test account to make it work!!!
 */
class ListRepositoryTest extends TestCase
{

    const MAILCHIMP_API_KEY = '3419ca97412af7c2893b89894275b415-us14';
    const LIST_ID = 'ba039c6198';

    protected $listRepository = null;

    public function setUp(){
        $mailchimp = new MailChimp(self::MAILCHIMP_API_KEY);
        $this->listRepository = new ListRepository($mailchimp);
    }

    public function testGetList()
    {
        $list = $this->listRepository->findById(self::LIST_ID);
        //var_dump($list);
        $this->assertNotEmpty($list);
        $this->assertEquals($list['id'], self::LIST_ID);
    }

    public function testSubscribe()
    {
        // /!\ if toto already exist, this test will throw an error...
        $subscriber = new Subscriber('toto@gmail.com', ['FNAME' => 'Toto', 'LNAME' => 'TEST'], ['language' => 'fr']);
        $result = $this->listRepository->subscribe(self::LIST_ID, $subscriber);
        //var_dump($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($result['email_address'], 'toto@gmail.com');
        $this->assertEquals($result['status'], "subscribed");
    }

    public function testUnsubscribe()
    {
        $subscriber = new Subscriber('toto@gmail.com', ['FNAME' => 'Toto', 'LNAME' => 'TEST'], ['language' => 'fr']);
        $result = $this->listRepository->unsubscribe(self::LIST_ID, $subscriber);
        //var_dump($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($result['email_address'], 'toto@gmail.com');
        $this->assertEquals($result['status'], "unsubscribed");
    }

    public function testDelete()
    {
        $subscriber = new Subscriber('toto@gmail.com', ['FNAME' => 'Toto', 'LNAME' => 'TEST'], ['language' => 'fr']);
        $result = $this->listRepository->delete(self::LIST_ID, $subscriber);
        $this->assertEmpty($result);
    }

    public function testBatchSubscribe()
    {
        $subscribers = [];
        $subscriber1 = new Subscriber('tata@gmail.com', ['FNAME' => 'Tata', 'LNAME' => 'TAST'], ['language' => 'fr']);
        array_push($subscribers, $subscriber1);
        $subscriber2 = new Subscriber('tete@gmail.com', ['FNAME' => 'Tete', 'LNAME' => 'TEEST'], ['language' => 'fr']);
        array_push($subscribers, $subscriber2);
        $subscriber3 = new Subscriber('tztz@gmail.com', ['FNAME' => 'Tztz', 'LNAME' => 'TZST'], ['language' => 'fr']);
        array_push($subscribers, $subscriber3);
        $subscriber4 = new Subscriber('trtr@gmail.com', ['FNAME' => 'Trtr', 'LNAME' => 'TRST'], ['language' => 'fr']);
        array_push($subscribers, $subscriber4);

        $result = $this->listRepository->batchSubscribe(self::LIST_ID, $subscribers);

        //var_dump($result);
        $this->assertNotEmpty($result);
    }

    public function testBatchUnsubscribe()
    {
        $emails = [
            'tata@gmail.com',
            'tete@gmail.com',
            'tztz@gmail.com'
        ];

        $result = $this->listRepository->batchUnsubscribe(self::LIST_ID, $emails);

        //var_dump($result);
        $this->assertNotEmpty($result);
    }

    public function testBatchDelete()
    {
        $emails = [
            'tata@gmail.com',
            'tete@gmail.com',
            'tztz@gmail.com',
            'trtr@gmail.com'
        ];

        $result = $this->listRepository->batchDelete(self::LIST_ID, $emails);

        //var_dump($result);
        $this->assertNotEmpty($result);
    }

}
?>
