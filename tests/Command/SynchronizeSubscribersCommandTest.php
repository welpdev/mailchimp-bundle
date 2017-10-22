<?php

// tests/AppBundle/Command/CreateUserCommandTest.php

namespace Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Welp\MailchimpBundle\Command\SynchronizeSubscribersCommand;

// @TODO make this test work...
class SynchronizeSubscribersCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        /*self::bootKernel();
        $application = new Application(self::$kernel);

        $application->add(new SynchronizeSubscribersCommand());

        $command = $application->find('welp:mailchimp:synchronize-subscribers');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),

            // pass arguments to the helper
            //'username' => 'Wouter',

            // prefix the key with a double slash when passing options,
            // e.g: '--some-option' => 'option_value',
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        //$this->assertContains('Username: Wouter', $output);

        */
    }
}
