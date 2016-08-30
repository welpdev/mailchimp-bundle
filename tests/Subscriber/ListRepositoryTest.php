<?php
use PHPUnit\Framework\TestCase;
use Welp\MailchimpBundle\Subscriber\ListRepository;
use Welp\MailchimpBundle\Subscriber\Subscriber;
use \DrewM\MailChimp\MailChimp;

/**
 * These integration tests work with a test MailChimp account
 * @TODO MailChimp API key and other variable in parameter to work with any MailChimp Account...
 */
class ListRepositoryTest extends TestCase
{
    protected $listRepository = null;

    public function setUp(){
        $mailchimp = new MailChimp('3419ca97412af7c2893b89894275b415-us14');
        $this->listRepository = new ListRepository($mailchimp);
    }

    public function testGetList()
    {
        $list = $this->listRepository->findById('ba039c6198');
        var_dump($list);
        $this->assertNotEmpty($list);
        $this->assertEquals($list['id'], 'ba039c6198');
    }

    public function testSubscribe()
    {
        // /!\ if toto already exist, this test will throw an error...
        $subscriber = new Subscriber('toto@gmail.com', ['FNAME' => 'Toto', 'LNAME' => 'TEST'], ['language' => 'fr']);
        $result = $this->listRepository->subscribe('ba039c6198', $subscriber);
        var_dump($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($result['email_address'], 'toto@gmail.com');
        $this->assertEquals($result['status'], "subscribed");
    }

    public function testUnsubscribe()
    {
        $subscriber = new Subscriber('toto@gmail.com', ['FNAME' => 'Toto', 'LNAME' => 'TEST'], ['language' => 'fr']);
        $result = $this->listRepository->unsubscribe('ba039c6198', $subscriber);
        var_dump($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($result['email_address'], 'toto@gmail.com');
        $this->assertEquals($result['status'], "unsubscribed");
    }

    public function testDelete()
    {
        $subscriber = new Subscriber('toto@gmail.com', ['FNAME' => 'Toto', 'LNAME' => 'TEST'], ['language' => 'fr']);
        $result = $this->listRepository->delete('ba039c6198', $subscriber);
        $this->assertEmpty($result);
    }

}
?>
