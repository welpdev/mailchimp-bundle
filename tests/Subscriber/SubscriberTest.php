<?php

namespace Tests\Subscriber;

use PHPUnit\Framework\TestCase;
use Welp\MailchimpBundle\Subscriber\Subscriber;

/**
 * Class SubscriberTest.
 */
class SubscriberTest extends TestCase
{
    /**
     * @param string $email
     * @param array  $fields
     * @param array  $options
     * @param array  $expected
     *
     * @dataProvider provider
     */
    public function testFormatMailChimp($email, array $fields, array $options, array $expected)
    {
        $result = $this->getSubscriber($email, $fields, $options)->formatMailChimp();

        static::assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function provider()
    {
        return [
            [
                'test@gmail.com',
                ['field1' => 'field1', 'nullable' => null],
                ['option' => 'option_value'],
                //expected merge_fields
                ['merge_fields' => ['field1' => 'field1'], 'option' => 'option_value', 'email_address' => 'test@gmail.com'],
            ],
        ];
    }

    /**
     * @param $email
     * @param $fields
     * @param $options
     *
     * @return Subscriber
     */
    private function getSubscriber($email, $fields, $options)
    {
        return new Subscriber($email, $fields, $options);
    }
}
